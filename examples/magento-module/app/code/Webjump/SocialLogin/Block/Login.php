<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webjump\SocialLogin\Helper\Data as SocialLoginHelper;

/**
 * Class Login
 * @package Webjump\SocialLogin\Block
 */
class Login extends Template
{
    /**
     * @var SocialLoginHelper
     */
    protected $socialLoginHelper;

    /**
     * @param Context $context
     * @param SocialLoginHelper $socialLoginHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SocialLoginHelper $socialLoginHelper,
        array $data = []
    ) {
        $this->socialLoginHelper = $socialLoginHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->socialLoginHelper->isEnabled();
    }

    /**
     * @return array
     */
    public function getEnabledProviders()
    {
        return $this->socialLoginHelper->getEnabledProviders();
    }

    /**
     * @return bool
     */
    public function hasEnabledProviders()
    {
        return !empty($this->getEnabledProviders());
    }

    /**
     * @return string
     */
    public function getWidgetConfigJson()
    {
        return $this->socialLoginHelper->getWidgetConfigJson();
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->socialLoginHelper->getApiEndpoint();
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->socialLoginHelper->getPosition();
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->socialLoginHelper->getTheme();
    }

    /**
     * Check if should render the block
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled() || !$this->hasEnabledProviders()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get provider display name
     *
     * @param string $provider
     * @return string
     */
    public function getProviderDisplayName($provider)
    {
        $displayNames = [
            'google' => __('Google'),
            'meta' => __('Facebook'),
            'linkedin' => __('LinkedIn'),
            'paypal' => __('PayPal'),
            'apple' => __('Apple'),
            'twitter' => __('Twitter'),
            'pinterest' => __('Pinterest'),
            'instagram' => __('Instagram'),
        ];

        return isset($displayNames[$provider]) ? $displayNames[$provider] : ucfirst($provider);
    }

    /**
     * Get container CSS classes
     *
     * @return string
     */
    public function getContainerCssClass()
    {
        $classes = ['webjump-social-login-container'];
        $classes[] = 'theme-' . $this->getTheme();
        $classes[] = 'size-' . $this->socialLoginHelper->getButtonSize();

        if (!$this->socialLoginHelper->getShowLabels()) {
            $classes[] = 'no-labels';
        }

        return implode(' ', $classes);
    }

    /**
     * Get Social Login Helper instance
     *
     * @return SocialLoginHelper
     */
    public function getSocialLoginHelper()
    {
        return $this->socialLoginHelper;
    }
}