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
         * Hand the Adobe Commerce customer token to the module controller, which
         * validates it against Commerce and opens the customer session.
         *
         * No client-side validation of the token is attempted on purpose: it is
         * an opaque Commerce token, and any check made here would be advisory
         * only - the server is what decides.
         *
         * @param {String} token - Adobe Commerce customer token
         * @param {String} returnUrl - URL to redirect to after authentication
         * @returns {Promise}
         */
        processCustomerToken: function (token, returnUrl) {
            var self = this;

            if (!token) {
                return Promise.reject('An authentication token is required');
            }

            var url = urlBuilder.build('sociallogin/auth/callback');
            var payload = {
                token: token,
                return_url: returnUrl || null
            };

            return storage.post(url, JSON.stringify(payload))
                .done(function (response) {
                    if (!response.success) {
                        self.handleError(response.message || 'Authentication failed');

                        return;
                    }

                    // Refresh the customer sections so the storefront reflects
                    // the new logged-in state.
                    customerData.invalidate(['customer']);
                    customerData.reload(['customer'], true);

                    $('body').trigger('socialLoginSuccess', {
                        customerId: response.customer_id,
                        redirectUrl: response.redirect_url
                    });

                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        window.location.reload();
                    }
                })
                .fail(function (xhr) {
                    var errorMessage = 'Authentication failed';

                    try {
                        errorMessage = JSON.parse(xhr.responseText).message || errorMessage;
                    } catch (e) {
                        // Keep the default message.
                    }

                    self.handleError(errorMessage);
                });
        },

        /**
         * Handle authentication errors
         *
         * @param {String} message - Error message
         */
        handleError: function (message) {
            $('body').trigger('socialLoginError', {
                message: message
            });

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
         * Wire the social login widget so a successful login opens a Magento
         * customer session.
         *
         * @param {Object} config - Widget configuration
         */
        initSocialLoginWidget: function (config) {
            var self = this,
                originalOnSuccess = config.onSuccess || function () {};

            config.onSuccess = function (data) {
                var token = data.token || null;

                if (!token) {
                    self.handleError('No Adobe Commerce token received from authentication');

                    return;
                }

                self.processCustomerToken(token, config.redirectUrl)
                    .then(function () {
                        originalOnSuccess(data);
                    })
                    .catch(function (error) {
                        self.handleError(error);
                    });
            };

            if (typeof SocialLoginWidget !== 'undefined') {
                new SocialLoginWidget(config);
            } else {
                self.handleError('Social Login widget script is not loaded');
            }
        }
    };
});
