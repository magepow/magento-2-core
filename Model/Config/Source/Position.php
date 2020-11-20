<?php

/**
 * @Author: Alex Dong
 * @Date:   2020-11-20 10:33:47
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-11-20 10:33:57
 */

namespace Magepow\Core\Model\Config\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'top',  'label'=>__('Top')),
            array('value' => 'right',  'label'=>__('Right')),
            array('value' => 'bottom',  'label'=>__('Bottom')),
            array('value' => 'left',   'label'=>__('Left'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
        	'1' => __('Top'), 
        	'2' => __('Right'),
        	'3' => __('Bottom'),
        	'4' => __('Left'),
        ];
    }

}
