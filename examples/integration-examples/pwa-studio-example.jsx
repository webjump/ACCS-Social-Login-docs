/**
 * Example integration of the Social Login Widget with PWA Studio
 * Copyright © Webjump. All rights reserved.
 */

import React, { useEffect, useRef, useState, useCallback } from 'react';
import { gql, useMutation, useQuery } from '@apollo/client';
import { useCartContext } from '@magento/peregrine/lib/context/cart';
import { useUserContext } from '@magento/peregrine/lib/context/user';
import { useToasts } from '@magento/peregrine';
import { mergeClasses } from '@magento/venia-ui/lib/classify';

import defaultClasses from './socialLogin.module.css';

// GraphQL Mutations
const CREATE_CUSTOMER_MUTATION = gql`
    mutation CreateCustomerFromSocial($input: CreateCustomerInput!) {
        createCustomer(input: $input) {
            customer {
                id
                email
                firstname
                lastname
            }
        }
    }
`;

const GENERATE_CUSTOMER_TOKEN_MUTATION = gql`
    mutation GenerateCustomerTokenFromSocial($email: String!, $password: String!) {
        generateCustomerToken(email: $email, password: $password) {
            token
        }
    }
`;

// GraphQL query for the widget configuration
const GET_SOCIAL_LOGIN_CONFIG = gql`
    query GetSocialLoginConfig {
        storeConfig {
            social_login_enabled
            social_login_providers
            social_login_theme
            social_login_button_size
            social_login_api_endpoint
        }
    }
`;

/**
 * Hook customizado para gerenciar Social Login
 */
const useSocialLogin = () => {
    const [, { addToast }] = useToasts();
    const [{ currentUser }, { getUserDetails, setToken }] = useUserContext();
    const [{ cartId }, { createCart, getCartDetails }] = useCartContext();

    const [createCustomer] = useMutation(CREATE_CUSTOMER_MUTATION);
    const [generateToken] = useMutation(GENERATE_CUSTOMER_TOKEN_MUTATION);

    const handleSocialLoginSuccess = useCallback(async (data) => {
        try {
            addToast({
                type: 'info',
                message: `Processando login via ${data.socialProvider}...`,
                timeout: 3000
            });

            let customerToken;

            if (data.action === 'created') {
                // Criar novo cliente
                const customerInput = {
                    email: data.user.email,
                    firstname: data.user.firstName || data.user.fullName.split(' ')[0] || 'Social',
                    lastname: data.user.lastName || data.user.fullName.split(' ').slice(1).join(' ') || 'User',
                    // Generate a temporary password to satisfy Commerce's requirements
                    password: Math.random().toString(36).substring(2, 15)
                };

                await createCustomer({
                    variables: { input: customerInput }
                });

                // Gerar token para o novo cliente
                const tokenResponse = await generateToken({
                    variables: {
                        email: customerInput.email,
                        password: customerInput.password
                    }
                });

                customerToken = tokenResponse.data.generateCustomerToken.token;
            } else {
                // Cliente existente, usar token retornado
                customerToken = data.token;
            }

            if (customerToken) {
                // Set the token in the user context
                await setToken(customerToken);

                // Refresh the user details
                await getUserDetails();

                // Merge the guest cart if there is one
                if (cartId) {
                    try {
                        await getCartDetails({ cartId });
                    } catch (error) {
                        console.warn('Cart merge failed:', error);
                        // Criar novo cart se falhar
                        await createCart();
                    }
                }

                addToast({
                    type: 'success',
                    message: `Login realizado com sucesso via ${data.socialProvider}!`,
                    timeout: 5000
                });

                // Fechar modal de login se existir
                if (typeof window !== 'undefined' && window.dispatchEvent) {
                    window.dispatchEvent(new Event('social-login-success'));
                }
            }

        } catch (error) {
            console.error('Social login post-processing error:', error);
            addToast({
                type: 'error',
                message: `Failed to process the login: ${error.message}`,
                timeout: 7000
            });
        }
    }, [setToken, getUserDetails, cartId, createCart, getCartDetails, addToast, createCustomer, generateToken]);

    const handleSocialLoginError = useCallback((error) => {
        console.error('Social login failed:', error);

        const errorMessages = {
            'network_error': 'Connection error. Please check your internet connection.',
            'auth_cancelled': 'Login cancelled.',
            'invalid_credentials': 'Invalid credentials. Please try again.',
            'server_error': 'Server error. Please try again later.'
        };

        const userMessage = errorMessages[error.code] || error.message;

        addToast({
            type: 'error',
            message: `Social login error: ${userMessage}`,
            timeout: 7000
        });
    }, [addToast]);

    return {
        handleSocialLoginSuccess,
        handleSocialLoginError,
        currentUser
    };
};

/**
 * Componente Social Login Widget para PWA Studio
 */
const SocialLoginWidget = (props) => {
    const classes = mergeClasses(defaultClasses, props.classes);
    const {
        containerId = `social-login-${Math.random().toString(36).substr(2, 9)}`,
        providers = ['google', 'meta'],
        theme = 'light',
        buttonSize = 'medium',
        showLabels = true,
        onSuccess,
        onError,
        className = '',
        ...restProps
    } = props;

    const widgetRef = useRef(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isScriptLoaded, setIsScriptLoaded] = useState(false);
    const [configError, setConfigError] = useState(null);

    // Hook personalizado
    const {
        handleSocialLoginSuccess,
        handleSocialLoginError,
        currentUser
    } = useSocialLogin();

    // Query the configuration exposed by the admin
    const { data: configData, loading: configLoading, error: configQueryError } = useQuery(GET_SOCIAL_LOGIN_CONFIG, {
        fetchPolicy: 'cache-and-network'
    });

    // Carregar script do widget
    useEffect(() => {
        if (!configData?.storeConfig?.social_login_enabled || isScriptLoaded) {
            return;
        }

        const apiEndpoint = configData.storeConfig.social_login_api_endpoint;
        if (!apiEndpoint) {
            setConfigError('API endpoint is not configured');
            setIsLoading(false);
            return;
        }

        const script = document.createElement('script');
        const widgetUrl = apiEndpoint.replace('/social-login', '/SocialLoginWidget.js');
        script.src = widgetUrl;

        script.onload = () => {
            setIsScriptLoaded(true);
            setIsLoading(false);
        };

        script.onerror = () => {
            setConfigError('Failed to load the widget script');
            setIsLoading(false);
        };

        document.head.appendChild(script);

        return () => {
            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }
        };
    }, [configData, isScriptLoaded]);

    // Inicializar widget quando script carrega
    useEffect(() => {
        if (!isScriptLoaded || !window.SocialLoginWidget || !configData) {
            return;
        }

        const config = configData.storeConfig;

        try {
            const enabledProviders = config.social_login_providers
                ? config.social_login_providers.split(',').map(p => p.trim())
                : providers;

            const widget = new window.SocialLoginWidget({
                containerId,
                apiEndpoint: config.social_login_api_endpoint,
                providers: enabledProviders,
                theme: config.social_login_theme || theme,
                buttonSize: config.social_login_button_size || buttonSize,
                showLabels: showLabels,
                onSuccess: onSuccess || handleSocialLoginSuccess,
                onError: onError || handleSocialLoginError
            });

            return () => {
                // Clean up the widget if needed
                if (widget && typeof widget.destroy === 'function') {
                    widget.destroy();
                }
            };
        } catch (error) {
            console.error('Error initializing social login widget:', error);
            setConfigError(`Failed to initialize the widget: ${error.message}`);
        }
    }, [
        isScriptLoaded,
        configData,
        containerId,
        providers,
        theme,
        buttonSize,
        showLabels,
        onSuccess,
        onError,
        handleSocialLoginSuccess,
        handleSocialLoginError
    ]);

    // Don't render when the user is already signed in
    if (currentUser && currentUser.email) {
        return null;
    }

    // Don't render when social login is disabled
    if (configData && !configData.storeConfig?.social_login_enabled) {
        return null;
    }

    // Estados de loading e erro
    if (configLoading || isLoading) {
        return (
            <div className={`${classes.container} ${className}`} {...restProps}>
                <div className={classes.loading}>
                    <div className={classes.spinner} />
                    <span>Loading login options...</span>
                </div>
            </div>
        );
    }

    if (configQueryError || configError) {
        return (
            <div className={`${classes.container} ${classes.error} ${className}`} {...restProps}>
                <div className={classes.errorMessage}>
                    ⚠️ {configQueryError?.message || configError}
                </div>
            </div>
        );
    }

    return (
        <div className={`${classes.container} ${className}`} {...restProps}>
            <div className={classes.divider}>
                <span>ou continue com</span>
            </div>
            <div
                id={containerId}
                ref={widgetRef}
                className={classes.widgetContainer}
            />
        </div>
    );
};

export default SocialLoginWidget;

// CSS Module: socialLogin.module.css
export const cssModule = `
.container {
    margin: 1rem 0;
    text-align: center;
}

.divider {
    position: relative;
    margin: 1.5rem 0;
    color: rgb(var(--venia-text-alt));
    font-size: 0.875rem;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgb(var(--venia-border-color));
}

.divider span {
    background: rgb(var(--venia-background));
    padding: 0 1rem;
    position: relative;
}

.widgetContainer {
    margin: 1rem 0;
}

.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    color: rgb(var(--venia-text-alt));
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgb(var(--venia-border-color));
    border-top: 2px solid rgb(var(--venia-brand-color-1));
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.error {
    padding: 1rem;
    background: rgb(var(--venia-error-background, 248, 215, 218));
    border: 1px solid rgb(var(--venia-error-border, 245, 198, 203));
    border-radius: 0.25rem;
}

.errorMessage {
    color: rgb(var(--venia-error-text, 114, 28, 36));
    font-size: 0.875rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .container {
        margin: 0.75rem 0;
    }

    .divider {
        margin: 1rem 0;
    }
}
`;

// Example usage inside the SignIn component
export const SignInWithSocialLogin = () => {
    return (
        <div className="sign-in-page">
            {/* Standard login form */}
            <form className="sign-in-form">
                {/* Email and password fields */}
            </form>

            {/* Social Login Widget */}
            <SocialLoginWidget
                providers={['google', 'meta']}
                theme="light"
                buttonSize="medium"
                showLabels={true}
            />
        </div>
    );
};

// Hook para uso em outros componentes
export { useSocialLogin };