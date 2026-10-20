<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Core helper: module configuration accessors.
 */
class Data extends AbstractHelper
{
    public const XML_PATH_NOTIFICATION = 'magepow_core/notification';

    /**
     * @var array
     */
    protected $configModule;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param Context $context
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        Context $context,
        ModuleManager $moduleManager
    ) {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->configModule = $this->getConfig(strtolower($this->_getModuleName()));
    }

    /**
     * Retrieve a store config value or the scope config object.
     *
     * @param string $path
     * @return mixed
     */
    public function getConfig($path = '')
    {
        if ($path) {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
        }
        return $this->scopeConfig;
    }

    /**
     * Retrieve a value from the current module configuration array.
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public function getConfigModule($path = '', $default = null)
    {
        $values = $this->configModule;
        if (!$path) {
            return $values;
        }
        $segments = explode('/', (string)$path);
        $lastIndex = count($segments) - 1;
        $result = $default;
        foreach ($segments as $index => $segment) {
            if (isset($values[$segment])) {
                if ($index === $lastIndex) {
                    $result = $values[$segment];
                } else {
                    $values = $values[$segment];
                }
            }
        }
        return $result;
    }

    /**
     * Check if a module is enabled.
     *
     * @param string $moduleName
     * @return bool
     */
    public function isEnabledModule($moduleName)
    {
        return $this->moduleManager->isEnabled($moduleName);
    }

    /**
     * Retrieve the current module name.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_getModuleName();
    }

    /**
     * Retrieve a notification config value, e.g. getNotificationConfig('update/severity').
     *
     * @param string $path
     * @return mixed
     */
    public function getNotificationConfig($path = '')
    {
        $fullPath = $path
            ? self::XML_PATH_NOTIFICATION . '/' . ltrim($path, '/')
            : self::XML_PATH_NOTIFICATION;
        return $this->scopeConfig->getValue($fullPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if notifications are enabled globally and for the given type.
     *
     * Both the master switch and the type switch must be enabled.
     *
     * @param string $type
     * @return bool
     */
    public function isNotificationEnabled($type = '')
    {
        $masterEnabled = (bool)$this->getNotificationConfig('enabled');
        if (!$masterEnabled) {
            return false;
        }
        if (!$type) {
            return true;
        }
        return (bool)$this->getNotificationConfig($type . '/enabled');
    }
}
