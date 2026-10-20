<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Model\System\Message;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;
use Magepow\Core\Helper\Data as CoreHelper;

/**
 * Admin system message: module update notice.
 *
 * Displayed when the latest available version (default in etc/config.xml)
 * is greater than the installed module version.
 */
class Update implements MessageInterface
{
    /**
     * Fallback used only when the config value is empty.
     * The primary value lives in etc/config.xml and is not exposed in the admin UI.
     */
    public const FALLBACK_LATEST_VERSION = '1.2.0';
    public const FALLBACK_INFO_URL = 'https://magepow.com/magento-2-extensions.html';

    /**
     * @var CoreHelper
     */
    private $helper;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param CoreHelper $helper
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        CoreHelper $helper,
        ModuleListInterface $moduleList
    ) {
        $this->helper = $helper;
        $this->moduleList = $moduleList;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity()
    {
        return md5('MAGEPOW_CORE_UPDATE_' . $this->getLatestVersion());
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed()
    {
        if (!$this->helper->isNotificationEnabled('update')) {
            return false;
        }
        $latestVersion = $this->getLatestVersion();
        return $latestVersion && version_compare($latestVersion, $this->getCurrentVersion(), '>');
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        $latestVersion = $this->getLatestVersion();
        $currentVersion = $this->getCurrentVersion();
        return __(
            'A new version of Magepow extensions is available: <strong>%1</strong> (current: %2). '
            . 'Please <a href="%3" target="_blank">update now</a> to get new features and security fixes.',
            $latestVersion,
            $currentVersion,
            self::FALLBACK_INFO_URL
        );
    }

    /**
     * @inheritdoc
     */
    public function getSeverity()
    {
        return (int)$this->helper->getNotificationConfig('update_severity') ?: self::SEVERITY_MAJOR;
    }

    /**
     * Retrieve the installed module version.
     *
     * @return string
     */
    private function getCurrentVersion()
    {
        $module = $this->moduleList->getOne('Magepow_Core');
        return $module['setup_version'] ?? '1.0.0';
    }

    /**
     * Retrieve the latest available version from configuration.
     *
     * The default value lives in etc/config.xml. There is no matching
     * field in the admin UI, so merchants cannot change it.
     *
     * @return string
     */
    private function getLatestVersion()
    {
        $latestVersion = trim((string)$this->helper->getNotificationConfig('update_latest_version'));
        return $latestVersion ?: self::FALLBACK_LATEST_VERSION;
    }
}
