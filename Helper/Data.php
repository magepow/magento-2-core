<?php
/**
 * @Author: nguyen
 * @Date:   2020-02-12 14:01:01
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-12-25 10:54:35
 */

namespace Magepow\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $configModule;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->configModule = $this->getConfig(strtolower($this->_getModuleName()));
    }

    public function getConfig($cfg='')
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }

    public function getConfigModule($cfg='', $value=null)
    {
        $values = $this->configModule;
        if( !$cfg ) return $values;
        $config  = explode('/', $cfg);
        $end     = count($config) - 1;
        foreach ($config as $key => $vl) {
            if( isset($values[$vl]) ){
                if( $key == $end ) {
                    $value = $values[$vl];
                }else {
                    $values = $values[$vl];
                }
            } 

        }
        return $value;
    }

    public function isEnabledModule($moduleName)
    {
        return $this->moduleManager->isEnabled($moduleName);
    }

}
