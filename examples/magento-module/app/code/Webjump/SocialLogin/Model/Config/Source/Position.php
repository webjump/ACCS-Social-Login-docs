<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Position
 * @package Webjump\SocialLogin\Model\Config\Source
 */
class Position implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'before_form', 'label' => __('Before Login Form')],
            ['value' => 'after_form', 'label' => __('After Login Form')],
            ['value' => 'replace_form', 'label' => __('Replace Login Form')],
            ['value' => 'custom', 'label' => __('Custom Position')]
        ];
    }
}