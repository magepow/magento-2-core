<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Model;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface as BackendConfig;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\Auth\Session as BackendSession;

/**
 * Admin inbox feed.
 *
 * Pulls the remote RSS/JSON feed and stores new items in the
 * adminnotification_inbox table, following the Magento_AdminNotification
 * contract.
 */
class Feed
{
    public const XML_FEED_URL_PATH = 'magepow_core/notification/feed_url';
    public const XML_FREQUENCY_PATH = 'magepow_core/notification/frequency';
    public const XML_LAST_UPDATE_PATH = 'magepow_core/notification/last_update';
    public const XML_ENABLED_PATH = 'magepow_core/notification/enabled';
    public const CACHE_KEY_LASTCHECK = 'magepow_core_notifications_lastcheck';

    /**
     * Hard fallback used only when the config value is empty.
     * The primary value lives in etc/config.xml and is not exposed in the admin UI.
     */
    public const FALLBACK_FEED_URL = 'https://magepow.com/community/notifications/feed/';
    public const FALLBACK_FREQUENCY = 172800;

    /**
     * @var BackendConfig
     */
    private $backendConfig;

    /**
     * @var InboxFactory
     */
    private $inboxFactory;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var NotificationConfig
     */
    private $notificationConfig;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param BackendConfig $backendConfig
     * @param InboxFactory $inboxFactory
     * @param Curl $curl
     * @param WriterInterface $configWriter
     * @param NotificationConfig $notificationConfig
     * @param BackendSession $backendSession
     * @param DeploymentConfig $deploymentConfig
     * @param ProductMetadataInterface $productMetadata
     * @param UrlInterface $urlBuilder
     * @param ModuleListInterface $moduleList
     * @param ModuleManager $moduleManager
     * @param CacheInterface $cache
     */
    public function __construct(
        BackendConfig $backendConfig,
        InboxFactory $inboxFactory,
        Curl $curl,
        WriterInterface $configWriter,
        NotificationConfig $notificationConfig,
        BackendSession $backendSession,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        ModuleListInterface $moduleList,
        ModuleManager $moduleManager,
        CacheInterface $cache
    ) {
        $this->backendConfig = $backendConfig;
        $this->inboxFactory = $inboxFactory;
        $this->curl = $curl;
        $this->configWriter = $configWriter;
        $this->notificationConfig = $notificationConfig;
        $this->backendSession = $backendSession;
        $this->deploymentConfig = $deploymentConfig;
        $this->productMetadata = $productMetadata;
        $this->urlBuilder = $urlBuilder;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->cache = $cache;
    }

    /**
     * Check if the notification feed is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->backendConfig->getValue(self::XML_ENABLED_PATH);
    }

    /**
     * Build the feed URL enriched with shop domain, installed Magepow module
     * versions and notification preferences.
     *
     * The base URL comes from configuration (default in etc/config.xml).
     * There is no matching field in the admin UI, so merchants cannot change it.
     *
     * @return string
     */
    public function getFeedUrl(): string
    {
        $base = trim((string)$this->backendConfig->getValue(self::XML_FEED_URL_PATH));
        if (!$base) {
            $base = self::FALLBACK_FEED_URL;
        }
        if (strpos($base, 'http') !== 0) {
            $base = 'https://' . ltrim($base, '/');
        }
        $base = rtrim($base, '/') . '/';
        $domain = '';
        try {
            $info = parse_url($this->urlBuilder->getBaseUrl());
            $domain = $info['host'] ?? '';
        } catch (\Throwable $exception) {
            $domain = '';
        }
        $url = $base . 'domain/' . urlencode((string)$domain);
        $modules = [];
        try {
            foreach ($this->moduleList->getAll() as $name => $module) {
                if (strpos($name, 'Magepow_') === 0 && $this->moduleManager->isEnabled($name)) {
                    $modules[] = str_replace('Magepow_', '', $name) . ',' . ($module['setup_version'] ?? '');
                }
            }
        } catch (\Throwable $exception) {
            $modules = [];
        }
        if ($modules) {
            $url .= '/modules/' . base64_encode(implode(';', $modules));
        }
        $preferences = [];
        try {
            foreach ($this->notificationConfig->receiveNotifications() as $key => $value) {
                $preferences[] = $key . ',' . (int)(bool)$value;
            }
        } catch (\Throwable $exception) {
            $preferences = [];
        }
        if ($preferences) {
            $url .= '/notifications/' . base64_encode(implode(';', $preferences));
        }
        return $url;
    }

    /**
     * Retrieve the raw feed URL from configuration (without enrichment).
     *
     * @return string
     */
    public function getBaseFeedUrl(): string
    {
        return (string)$this->backendConfig->getValue(self::XML_FEED_URL_PATH);
    }

    /**
     * Retrieve the check frequency in seconds.
     *
     * @return int
     */
    public function getFrequency(): int
    {
        $hours = (int)$this->backendConfig->getValue(self::XML_FREQUENCY_PATH);
        if ($hours <= 0) {
            return self::FALLBACK_FREQUENCY;
        }
        return $hours * 3600;
    }

    /**
     * Retrieve the last successful update timestamp.
     *
     * @return int
     */
    public function getLastUpdate(): int
    {
        try {
            $cached = $this->cache->load(self::CACHE_KEY_LASTCHECK);
            if ($cached) {
                return (int)$cached;
            }
        } catch (\Throwable $exception) {
            // Fall through to the persisted config value.
        }
        $value = $this->backendConfig->getValue(self::XML_LAST_UPDATE_PATH);
        return $value ? (int)$value : 0;
    }

    /**
     * Persist the last update timestamp in cache and configuration.
     *
     * @param int|null $time
     * @return $this
     */
    public function setLastUpdate(?int $time = null): self
    {
        $time = $time ?? time();
        try {
            $this->cache->save((string)$time, self::CACHE_KEY_LASTCHECK);
        } catch (\Throwable $exception) {
            // Cache failures must not break the admin area.
        }
        $this->configWriter->save(
            self::XML_LAST_UPDATE_PATH,
            (string)$time,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        return $this;
    }

    /**
     * Check the remote feed and import new items into the admin inbox.
     *
     * Uses a double guard (backend session plus persisted timestamp) so every
     * admin page load does not trigger a remote request. Respects the merchant
     * opt-out preferences and never breaks the admin area on feed errors.
     *
     * @param bool $force
     * @return $this
     */
    public function checkUpdate(bool $force = false): self
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        $time = time();
        $frequency = $this->getFrequency();
        $sessionLastUpdate = 0;
        try {
            $sessionLastUpdate = (int)$this->backendSession->getMagepowNoticeLastUpdate();
        } catch (\Throwable $exception) {
            $sessionLastUpdate = 0;
        }
        if (!$force
            && (($frequency + $sessionLastUpdate > $time) || ($frequency + $this->getLastUpdate() > $time))
        ) {
            return $this;
        }
        try {
            $this->backendSession->setMagepowNoticeLastUpdate($time);
        } catch (\Throwable $exception) {
            // Session failures must not break the admin area.
        }
        $wantsAnyNotification = false;
        try {
            foreach ($this->notificationConfig->receiveNotifications() as $value) {
                if ($value) {
                    $wantsAnyNotification = true;
                    break;
                }
            }
        } catch (\Throwable $exception) {
            $wantsAnyNotification = true;
        }
        if (!$wantsAnyNotification) {
            $this->setLastUpdate($time);
            return $this;
        }
        try {
            if (!$this->moduleManager->isEnabled('Magento_AdminNotification')) {
                return $this;
            }
            $url = $this->getFeedUrl();
            if (!$url) {
                return $this;
            }
            $this->curl->setTimeout(10);
            try {
                $this->curl->addHeader(
                    'User-Agent',
                    $this->productMetadata->getName() . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')'
                );
                $this->curl->addHeader('Referer', $this->urlBuilder->getUrl('*/*/*'));
            } catch (\Throwable $exception) {
                // Optional headers must not break the request.
            }
            $this->curl->get($url);
            $body = (string)$this->curl->getBody();
            $feedData = $this->filterByInstallDate($this->parseBody($body));
            if ($feedData) {
                $this->inboxFactory->create()->parse(array_reverse($feedData));
            }
        } catch (\Throwable $exception) {
            // Feed errors must never break the admin area.
        }
        $this->setLastUpdate($time);
        return $this;
    }

    /**
     * Keep only items newer than the installation date.
     *
     * Prevents importing outdated announcements on fresh installations.
     *
     * @param array $items
     * @return array
     */
    private function filterByInstallDate(array $items): array
    {
        try {
            $installDate = strtotime(
                (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE)
            );
        } catch (\Throwable $exception) {
            return $items;
        }
        if (!$installDate || $installDate > time() + 86400) {
            return $items;
        }
        $result = [];
        foreach ($items as $item) {
            $itemTime = strtotime($item['date_added'] ?? '');
            if ($itemTime && $itemTime < $installDate) {
                continue;
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Escape a feed value for safe admin output.
     *
     * @param string $value
     * @return string
     */
    private function escapeValue(string $value): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Parse a JSON or RSS response body into inbox rows.
     *
     * @param string $body
     * @return array
     * @throws LocalizedException
     */
    private function parseBody(string $body): array
    {
        $body = trim($body);
        if (!$body) {
            return [];
        }
        // Try JSON first.
        if ($body[0] === '{' || $body[0] === '[') {
            $decoded = json_decode($body, true);
            $items = is_array($decoded) ? ($decoded['items'] ?? $decoded['data'] ?? $decoded) : [];
            if (isset($items['items']) && is_array($items['items'])) {
                $items = $items['items'];
            }
            if (is_array($items)) {
                $result = [];
                foreach ($items as $item) {
                    if (!is_array($item) || empty($item['title'])) {
                        continue;
                    }
                    $result[] = [
                        'severity' => (int)($item['severity'] ?? MessageInterface::SEVERITY_NOTICE),
                        'date_added' => $item['date_added'] ?? date('Y-m-d H:i:s'),
                        'title' => $this->escapeValue((string)$item['title']),
                        'description' => $this->escapeValue((string)($item['description'] ?? '')),
                        'url' => $this->escapeValue((string)($item['url'] ?? ($item['link'] ?? ''))),
                    ];
                }
                return $result;
            }
        }
        // Fall back to RSS/XML.
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        if ($xml && isset($xml->channel->item)) {
            $result = [];
            foreach ($xml->channel->item as $item) {
                $publicationDate = strtotime((string)$item->pubDate) ?: time();
                $result[] = [
                    'severity' => (int)($item->severity ?? MessageInterface::SEVERITY_NOTICE),
                    'date_added' => date('Y-m-d H:i:s', $publicationDate),
                    'title' => $this->escapeValue((string)$item->title),
                    'description' => $this->escapeValue((string)$item->description),
                    'url' => $this->escapeValue((string)$item->link),
                ];
            }
            return $result;
        }
        return [];
    }
}
