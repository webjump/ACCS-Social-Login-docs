/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Customer/js/customer-data'
], function ($, urlBuilder, storage, customerData) {
    'use strict';

    return {
        /**
         * Process JWT token received from social login
         * @param {string} token - JWT token
         * @param {string} returnUrl - URL to redirect after successful authentication
         * @returns {Promise}
         */
        processJwtToken: function (token, returnUrl) {
            var self = this;

            if (!token) {
                return Promise.reject('JWT token is required');
            }

            var url = urlBuilder.build('sociallogin/auth/callback');
            var payload = {
                token: token,
                return_url: returnUrl || null
            };

            return storage.post(url, JSON.stringify(payload))
                .done(function (response) {
                    if (response.success) {
                        console.log('JWT Authentication successful:', response);

                        // Verify session ID was created
                        if (response.session_id) {
                            console.log('Session ID created:', response.session_id);
                        }

                        // Invalidate customer data cache to refresh login state
                        customerData.invalidate(['customer']);

                        // Reload customer sections
                        customerData.reload(['customer'], true);

                        // Wait for customer data to reload before redirecting
                        setTimeout(function() {
                            // Verify customer is logged in
                            var customer = customerData.get('customer')();

                            if (customer && customer.firstname) {
                                console.log('Customer data verified:', customer);

                                // Trigger custom event for other components
                                $('body').trigger('socialLoginSuccess', {
                                    customerId: response.customer_id,
                                    sessionId: response.session_id,
                                    redirectUrl: response.redirect_url,
                                    customer: customer
                                });

                                // Redirect if URL provided
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    // Reload current page to update login state
                                    window.location.reload();
                                }
                            } else {
                                console.warn('Customer data not loaded, forcing page reload');
                                window.location.reload();
                            }
                        }, 1000); // Wait 1 second for customer data to load

                    } else {
                        self.handleError(response.message || 'Authentication failed');
                    }
                })
                .fail(function (xhr) {
                    var errorMessage = 'Authentication failed';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        // Use default error message
                    }
                    self.handleError(errorMessage);
                });
        },

        /**
         * Handle authentication errors
         * @param {string} message - Error message
         */
        handleError: function (message) {
            console.error('Social Login Error:', message);

            // Trigger custom error event
            $('body').trigger('socialLoginError', {
                message: message
            });

            // Show error message to user
            if (window.require) {
                require(['Magento_Ui/js/model/messageList'], function (messageList) {
                    messageList.addErrorMessage({
                        message: message
                    });
                });
            } else {
                alert(message);
            }
        },

        /**
         * Initialize JWT authentication for social login widget
         * @param {Object} config - Widget configuration
         */
        initSocialLoginWidget: function (config) {
            var self = this;

            // Override success callback in widget config
            var originalOnSuccess = config.onSuccess || function () {};

            config.onSuccess = function (data) {
                console.log('Widget success callback received:', data);

                // Extract token from the response data
                var token = data.token || data.jwt || null;

                if (!token) {
                    console.error('No JWT token found in success data:', data);
                    self.handleError('JWT token not received from authentication');
                    return;
                }

                console.log('JWT token extracted:', token);

                // Process JWT token through Magento
                self.processJwtToken(token, config.redirectUrl)
                    .then(function () {
                        // Call original success callback if provided
                        originalOnSuccess(data);
                    })
                    .catch(function (error) {
                        console.error('JWT processing failed:', error);
                        self.handleError(error);
                    });
            };

            // Initialize the social login widget with updated config
            if (typeof SocialLoginWidget !== 'undefined') {
                new SocialLoginWidget(config);
            } else {
                console.error('SocialLoginWidget not found');
            }
        },

        /**
         * Validate JWT token structure (client-side basic validation)
         * @param {string} token - JWT token
         * @returns {boolean}
         */
        isValidJwtStructure: function (token) {
            if (!token || typeof token !== 'string') {
                return false;
            }

            var parts = token.split('.');
            if (parts.length !== 3) {
                return false;
            }

            try {
                // Try to decode header and payload
                var header = JSON.parse(atob(parts[0]));
                var payload = JSON.parse(atob(parts[1]));

                // Check for required claims
                return !!(payload.exp && payload.email);
            } catch (e) {
                return false;
            }
        },

        /**
         * Extract user info from JWT token (client-side)
         * @param {string} token - JWT token
         * @returns {Object|null}
         */
        extractUserInfo: function (token) {
            if (!this.isValidJwtStructure(token)) {
                return null;
            }

            try {
                var payload = JSON.parse(atob(token.split('.')[1]));
                return {
                    email: payload.email,
                    name: payload.name || payload.given_name + ' ' + payload.family_name,
                    picture: payload.picture,
                    provider: payload.provider,
                    exp: payload.exp
                };
            } catch (e) {
                return null;
            }
        }
    };
});