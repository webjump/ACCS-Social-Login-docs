<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Theme
 * @package Webjump\SocialLogin\Model\Config\Source
 */
class Theme implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'light', 'label' => __('Light')],
            ['value' => 'dark', 'label' => __('Dark')],
            ['value' => 'default', 'label' => __('Default')]
        ];
    }
}