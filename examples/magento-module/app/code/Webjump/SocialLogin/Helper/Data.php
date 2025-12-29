<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Webjump\SocialLogin\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    const XML_PATH_ENABLED = 'webjump_social_login/general/enabled';
    const XML_PATH_API_ENDPOINT = 'webjump_social_login/general/api_endpoint';
    const XML_PATH_API_BASE_ENDPOINT = 'webjump_social_login/general/api_base_endpoint';

    const XML_PATH_GOOGLE_ENABLED = 'webjump_social_login/providers/google_enabled';
    const XML_PATH_META_ENABLED = 'webjump_social_login/providers/meta_enabled';

    const XML_PATH_THEME = 'webjump_social_login/design/theme';
    const XML_PATH_BUTTON_SIZE = 'webjump_social_login/design/button_size';
    const XML_PATH_SHOW_LABELS = 'webjump_social_login/design/show_labels';
    const XML_PATH_POSITION = 'webjump_social_login/design/position';

    const XML_PATH_REDIRECT_URL = 'webjump_social_login/advanced/redirect_url';
    const XML_PATH_DEBUG_MODE = 'webjump_social_login/advanced/debug_mode';
    const XML_PATH_AUTO_CREATE = 'webjump_social_login/advanced/auto_create_customer';

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param null|string|bool|int $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getApiEndpoint($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_ENDPOINT, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getApiBaseEndpoint($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_BASE_ENDPOINT, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get enabled providers array
     *
     * @param null|string|bool|int $store
     * @return array
     */
    public function getEnabledProviders($store = null)
    {
        $providers = [];
        $providerConfigs = [
            'google' => self::XML_PATH_GOOGLE_ENABLED,
            'meta' => self::XML_PATH_META_ENABLED,
        ];

        foreach ($providerConfigs as $provider => $configPath) {
            if ($this->scopeConfig->isSetFlag($configPath, ScopeInterface::SCOPE_STORE, $store)) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getTheme($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_THEME, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getButtonSize($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BUTTON_SIZE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return bool
     */
    public function getShowLabels($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHOW_LABELS, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getPosition($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_POSITION, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return string
     */
    public function getRedirectUrl($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REDIRECT_URL, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return bool
     */
    public function isDebugMode($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG_MODE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null|string|bool|int $store
     * @return bool
     */
    public function getAutoCreateCustomer($store = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_AUTO_CREATE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get widget configuration as JSON
     *
     * @param null|string|bool|int $store
     * @return string
     */
    public function getWidgetConfigJson($store = null)
    {
        $config = [
            'containerId' => 'webjump-social-login-widget',
            'apiEndpoint' => $this->getApiEndpoint($store),
            'providers' => $this->getEnabledProviders($store),
            'theme' => $this->getTheme($store),
            'buttonSize' => $this->getButtonSize($store),
            'showLabels' => $this->getShowLabels($store),
            'redirectUrl' => $this->getRedirectUrl($store) ?: null,
            'debug' => $this->isDebugMode($store),
        ];

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get possible widget script URLs with fallbacks
     *
     * @param null|string|bool|int $store
     * @return array
     */
    public function getWidgetScriptUrls($store = null)
    {
        $baseUrl = $this->getApiBaseEndpoint($store);

        return [
            $baseUrl . '/example.a5bef67c.js', // TODO: Change to static filename
        ];
    }

    /**
     * Get JavaScript code for loading widget with fallbacks
     *
     * @param null|string|bool|int $store
     * @return string
     */
    public function getWidgetLoaderScript($store = null)
    {
        $scriptUrls = $this->getWidgetScriptUrls($store);
        $scriptUrlsJson = json_encode($scriptUrls, JSON_UNESCAPED_SLASHES);

        return "
        function loadSocialLoginWidget(config) {
            var scriptSources = {$scriptUrlsJson};
            var currentIndex = 0;

            function tryLoadScript() {
                if (currentIndex >= scriptSources.length) {
                    console.error('Failed to load Social Login Widget from all sources');
                    return;
                }

                var script = document.createElement('script');
                script.src = scriptSources[currentIndex];

                script.onload = function() {
                    function initializeWidget() {
                        if (typeof SocialLoginWidget !== 'undefined') {
                            // Use JWT authentication if available
                            if (typeof require !== 'undefined') {
                                require(['Webjump_SocialLogin/js/jwt-auth'], function(jwtAuth) {
                                    jwtAuth.initSocialLoginWidget(config);
                                });
                            } else {
                                new SocialLoginWidget(config);
                            }
                        } else {
                            setTimeout(initializeWidget, 100);
                        }
                    }
                    initializeWidget();
                };

                script.onerror = function() {
                    currentIndex++;
                    tryLoadScript();
                };

                document.head.appendChild(script);
            }

            if (typeof SocialLoginWidget !== 'undefined') {
                // Use JWT authentication if available
                if (typeof require !== 'undefined') {
                    require(['Webjump_SocialLogin/js/jwt-auth'], function(jwtAuth) {
                        jwtAuth.initSocialLoginWidget(config);
                    });
                } else {
                    new SocialLoginWidget(config);
                }
            } else {
                tryLoadScript();
            }
        }";
    }

    /**
     * Get JWT authentication callback URL
     *
     * @param null|string|bool|int $store
     * @return string
     */
    public function getJwtCallbackUrl($store = null)
    {
        return $this->_urlBuilder->getUrl('sociallogin/auth/callback', ['_store' => $store]);
    }
}
