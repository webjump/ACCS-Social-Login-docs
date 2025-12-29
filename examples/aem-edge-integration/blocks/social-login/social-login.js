/**
 * Social Login Block para AEM Edge Delivery Services + Adobe Commerce
 * Baseado no aem-boilerplate-commerce
 * @see https://github.com/hlxsites/aem-boilerplate-commerce
 */

import { getConfigValue } from '../../scripts/configs.js';
import { getCustomer, setCustomerToken } from '../../scripts/customer.js';
import { getCartId, mergeGuestCart } from '../../scripts/minicart.js';
import { performMonolithGraphQLQuery } from '../../scripts/commerce.js';

// GraphQL Mutations for Adobe Commerce
const CREATE_CUSTOMER_MUTATION = `
  mutation CreateCustomerFromSocial($input: CustomerInput!) {
    createCustomer(input: $input) {
      customer {
        id
        email
        firstname
        lastname
        is_subscribed
        created_at
      }
    }
  }
`;

const GENERATE_CUSTOMER_TOKEN_MUTATION = `
  mutation GenerateCustomerTokenFromSocial($email: String!, $password: String!) {
    generateCustomerToken(email: $email, password: $password) {
      token
    }
  }
`;

const MERGE_CARTS_MUTATION = `
  mutation MergeCarts($sourceCartId: String!, $destinationCartId: String!) {
    mergeCarts(
      source_cart_id: $sourceCartId
      destination_cart_id: $destinationCartId
    ) {
      id
      items {
        id
        quantity
        product {
          name
          sku
        }
      }
      total_quantity
    }
  }
`;

/**
 * Processar login social no contexto AEM Edge + Commerce
 */
async function processSocialLogin(socialData, guestCartId) {
  try {
    console.log('Processing social login in AEM Edge:', {
      provider: socialData.socialProvider,
      email: socialData.user.email,
      action: socialData.action
    });

    const email = socialData.user.email;
    const names = (socialData.user.fullName || '').split(' ');
    const firstName = socialData.user.firstName || names[0] || 'Social';
    const lastName = socialData.user.lastName || names.slice(1).join(' ') || 'User';

    // Generate temporary password for Magento compliance
    const tempPassword = generateSecurePassword();

    // 1. Create customer in Adobe Commerce
    const createCustomerResult = await performMonolithGraphQLQuery(
      CREATE_CUSTOMER_MUTATION,
      {
        input: {
          firstname: firstName,
          lastname: lastName,
          email: email,
          password: tempPassword,
          is_subscribed: socialData.user.isEmailVerified || false
        }
      }
    );

    if (!createCustomerResult.data?.createCustomer?.customer) {
      throw new Error('Failed to create customer in Adobe Commerce');
    }

    const customer = createCustomerResult.data.createCustomer.customer;

    // 2. Generate customer token
    const tokenResult = await performMonolithGraphQLQuery(
      GENERATE_CUSTOMER_TOKEN_MUTATION,
      {
        email: email,
        password: tempPassword
      }
    );

    if (!tokenResult.data?.generateCustomerToken?.token) {
      throw new Error('Failed to generate customer token');
    }

    const customerToken = tokenResult.data.generateCustomerToken.token;

    // 3. Set customer token in AEM Edge context
    setCustomerToken(customerToken);

    // 4. Merge guest cart if exists
    if (guestCartId) {
      try {
        await mergeGuestCart(guestCartId);
      } catch (cartError) {
        console.warn('Cart merge failed, but login succeeded:', cartError);
      }
    }

    // 5. Update customer data in local context
    window.adobeDataLayer = window.adobeDataLayer || [];
    window.adobeDataLayer.push({
      event: 'social-login-success',
      customer: {
        id: customer.id,
        email: customer.email,
        firstName: customer.firstname,
        lastName: customer.lastname,
        provider: socialData.socialProvider,
        isNewCustomer: socialData.action === 'created'
      }
    });

    // 6. Show success message
    showNotification(`Login realizado com sucesso via ${socialData.socialProvider}!`, 'success');

    // 7. Redirect to account page or reload
    setTimeout(() => {
      const redirectUrl = getConfigValue('social-login-redirect-url') || '/customer/account/';
      window.location.href = redirectUrl;
    }, 1500);

    return {
      customer,
      token: customerToken,
      provider: socialData.socialProvider,
      isNewCustomer: socialData.action === 'created'
    };

  } catch (error) {
    console.error('Social login processing error:', error);

    // Handle specific errors for AEM Edge
    let errorMessage = 'Erro no login social. Tente novamente.';

    if (error.message.includes('email') || error.message.includes('Email')) {
      errorMessage = 'Este email já está cadastrado. Faça login tradicional primeiro.';
    } else if (error.message.includes('network') || error.message.includes('connection')) {
      errorMessage = 'Erro de conexão. Verifique sua internet.';
    }

    showNotification(errorMessage, 'error');
    throw error;
  }
}

/**
 * Load Social Login Widget script
 */
async function loadSocialLoginWidget() {
  const apiEndpoint = getConfigValue('social-login-api-endpoint');
  if (!apiEndpoint) {
    throw new Error('Social Login API endpoint not configured');
  }

  return new Promise((resolve, reject) => {
    if (window.SocialLoginWidget) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    const scriptUrl = apiEndpoint.replace('/social-login', '/SocialLoginWidget.js');
    script.src = scriptUrl;
    script.async = true;

    script.onload = () => resolve();
    script.onerror = () => reject(new Error('Failed to load Social Login Widget script'));

    document.head.appendChild(script);
  });
}

/**
 * Initialize Social Login Widget
 */
function initializeSocialLoginWidget(container) {
  const containerId = `social-login-${Math.random().toString(36).substr(2, 9)}`;
  const widgetContainer = container.querySelector('.social-login-widget-container');
  widgetContainer.id = containerId;

  // Get configuration from AEM Edge configs
  const providers = getConfigValue('social-login-providers')?.split(',').map(p => p.trim()) || ['google', 'meta'];
  const theme = getConfigValue('social-login-theme') || 'light';
  const buttonSize = getConfigValue('social-login-button-size') || 'medium';
  const showLabels = getConfigValue('social-login-show-labels') !== 'false';
  const apiEndpoint = getConfigValue('social-login-api-endpoint');

  if (!apiEndpoint) {
    console.error('Social Login API endpoint not configured');
    showError(container, 'Configuração do Social Login não encontrada');
    return;
  }

  const widget = new window.SocialLoginWidget({
    containerId,
    apiEndpoint,
    providers,
    theme,
    buttonSize,
    showLabels,

    onSuccess: async (socialData) => {
      try {
        showLoading(container, true);

        // Get current guest cart ID if exists
        const guestCartId = getCartId();

        // Process social login
        await processSocialLogin(socialData, guestCartId);

      } catch (error) {
        console.error('Social login success handler error:', error);
        showError(container, error.message);
      } finally {
        showLoading(container, false);
      }
    },

    onError: (error) => {
      console.error('Social login widget error:', error);

      const friendlyErrors = {
        'network_error': 'Erro de conexão. Verifique sua internet.',
        'auth_cancelled': 'Login cancelado pelo usuário.',
        'invalid_credentials': 'Credenciais inválidas. Tente novamente.',
        'server_error': 'Erro no servidor. Tente novamente mais tarde.'
      };

      const errorMessage = friendlyErrors[error.code] || error.message;
      showNotification(errorMessage, 'error');
    }
  });

  return widget;
}

/**
 * Show loading state
 */
function showLoading(container, show) {
  const loadingElement = container.querySelector('.social-login-loading');
  const widgetContainer = container.querySelector('.social-login-widget-container');

  if (show) {
    loadingElement.style.display = 'flex';
    widgetContainer.style.display = 'none';
  } else {
    loadingElement.style.display = 'none';
    widgetContainer.style.display = 'block';
  }
}

/**
 * Show error state
 */
function showError(container, message) {
  const errorElement = container.querySelector('.social-login-error');
  const errorMessageElement = errorElement.querySelector('.error-message');
  const widgetContainer = container.querySelector('.social-login-widget-container');

  errorMessageElement.textContent = message;
  errorElement.style.display = 'block';
  widgetContainer.style.display = 'none';
}

/**
 * Show notification (AEM Edge compatible)
 */
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <span class="notification-icon">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
      <span class="notification-message">${message}</span>
    </div>
  `;

  // Add to page
  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);

  // Add click to close
  notification.addEventListener('click', () => {
    notification.remove();
  });
}

/**
 * Generate secure password for Magento compliance
 */
function generateSecurePassword() {
  const length = 12;
  const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
  let password = 'Aem1!'; // Ensure minimum requirements

  for (let i = password.length; i < length; i++) {
    password += charset.charAt(Math.floor(Math.random() * charset.length));
  }

  return password.split('').sort(() => Math.random() - 0.5).join('');
}

/**
 * Main block decorator function (AEM Edge pattern)
 */
export default async function decorate(block) {
  // Check if customer is already logged in
  const customer = await getCustomer();
  if (customer?.email) {
    block.style.display = 'none';
    return;
  }

  // Check if social login is enabled
  const socialLoginEnabled = getConfigValue('social-login-enabled');
  if (socialLoginEnabled === 'false') {
    block.style.display = 'none';
    return;
  }

  // Create block HTML structure
  block.innerHTML = `
    <div class="social-login-container">
      <div class="social-login-divider">
        <span>ou continue com</span>
      </div>

      <div class="social-login-loading" style="display: none;">
        <div class="loading-spinner"></div>
        <span>Carregando opções de login...</span>
      </div>

      <div class="social-login-error" style="display: none;">
        <div class="error-content">
          <span class="error-icon">⚠️</span>
          <span class="error-message"></span>
        </div>
        <button class="retry-button" onclick="location.reload()">Tentar Novamente</button>
      </div>

      <div class="social-login-widget-container"></div>

      <div class="social-login-footer">
        <p>Conecte-se rapidamente usando suas contas existentes</p>
      </div>
    </div>
  `;

  // Add CSS classes for AEM Edge styling
  block.classList.add('social-login-block');

  try {
    // Load and initialize widget
    showLoading(block, true);
    await loadSocialLoginWidget();
    initializeSocialLoginWidget(block);
    showLoading(block, false);

  } catch (error) {
    console.error('Failed to initialize social login block:', error);
    showError(block, 'Erro ao carregar Social Login');
  }
}

/**
 * Block configuration for AEM Edge
 * Add this to your configs.js file:
 */
export const socialLoginConfigs = {
  'social-login-enabled': 'true',
  'social-login-api-endpoint': 'https://your-app-domain.adobe.io/api/v1/social-login',
  'social-login-providers': 'google,meta',
  'social-login-theme': 'light',
  'social-login-button-size': 'medium',
  'social-login-show-labels': 'true',
  'social-login-redirect-url': '/customer/account/'
};