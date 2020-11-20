<?php

/**
 * @Author: Alex Dong
 * @Date:   2020-11-20 10:33:07
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-11-20 10:36:51
 */

namespace Magepow\Core\Model\Config\Source;

class Module implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * ConfigOption constructor.
     * @param ModuleList $moduleList
     */
    public function __construct(
        \Magento\Framework\Module\ModuleList $moduleList
    )
    {
        $this->moduleList = $moduleList;
    }

    public function toOptionArray()
    {
        $modules = $this->getAllModules();
        $options = [];
        foreach($modules as $module){
            $options[] = ['value' => $module, 'label' => $module];
        }
        return $options;
    }

    public function getAllModules()
    {
        return $this->moduleList->getNames();
    }
}