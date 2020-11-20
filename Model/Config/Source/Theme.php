<?php

/**
 * @Author: nguyen
 * @Date:   2020-06-12 08:42:03
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-11-20 10:35:14
 */

namespace Magepow\Core\Model\Config\Source;
use Magento\Theme\Model\Theme\Collection;
use Magento\Framework\App\Area;

class Theme implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $themesCollections = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Theme\Model\Theme\Collection');
        $themesCollections->addConstraint(Collection::CONSTRAINT_AREA, Area::AREA_FRONTEND);
        $themes = [];
        foreach ($themesCollections as $key => $value) {
            $themes[$value->getData('theme_path')] = $value->getData('theme_title');
        }
        natsort($themes);
        return $themes;
    }
}
