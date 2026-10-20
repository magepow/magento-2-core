<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Notification opt-in preferences.
 *
 * Allows merchants to choose which notification types they receive.
 */
class NotificationConfig
{
    public const XML_PATH_RECEIVE_UPDATE = 'magepow_core/notification/receive/update';
    public const XML_PATH_RECEIVE_SECURITY = 'magepow_core/notification/receive/security';
    public const XML_PATH_RECEIVE_NEWS = 'magepow_core/notification/receive/news';
    public const XML_PATH_RECEIVE_OFFER = 'magepow_core/notification/receive/offer';
    public const XML_PATH_RECEIVE_GENERAL = 'magepow_core/notification/receive/general';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if product update notifications are enabled.
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function receiveUpdate($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_RECEIVE_UPDATE, $storeId);
    }

    /**
     * Check if security notifications are enabled.
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function receiveSecurity($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_RECEIVE_SECURITY, $storeId);
    }

    /**
     * Check if news notifications are enabled.
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function receiveNews($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_RECEIVE_NEWS, $storeId);
    }

    /**
     * Check if special offer notifications are enabled.
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function receiveOffers($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_RECEIVE_OFFER, $storeId);
    }

    /**
     * Check if general notifications are enabled.
     *
     * @param int|string|null $storeId
     * @return mixed
     */
    public function receiveGeneral($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_RECEIVE_GENERAL, $storeId);
    }

    /**
     * Retrieve all notification preferences keyed by type.
     *
     * @param int|string|null $storeId
     * @return array
     */
    public function receiveNotifications($storeId = null): array
    {
        return [
            'update' => $this->receiveUpdate($storeId),
            'security' => $this->receiveSecurity($storeId),
            'news' => $this->receiveNews($storeId),
            'offer' => $this->receiveOffers($storeId),
            'general' => $this->receiveGeneral($storeId),
        ];
    }

    /**
     * Retrieve a store config value.
     *
     * @param string $path
     * @param int|string|null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
