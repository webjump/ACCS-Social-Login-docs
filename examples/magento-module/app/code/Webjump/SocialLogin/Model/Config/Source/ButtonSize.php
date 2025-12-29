<?php
/**
 * Copyright © Webjump. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webjump\SocialLogin\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ButtonSize
 * @package Webjump\SocialLogin\Model\Config\Source
 */
class ButtonSize implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'small', 'label' => __('Small')],
            ['value' => 'medium', 'label' => __('Medium')],
            ['value' => 'large', 'label' => __('Large')]
        ];
    }
}