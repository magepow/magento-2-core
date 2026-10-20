<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Source model: admin notification severity options.
 */
class Severity implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Critical')],
            ['value' => 2, 'label' => __('Major')],
            ['value' => 3, 'label' => __('Minor')],
            ['value' => 4, 'label' => __('Notice')],
        ];
    }
}
