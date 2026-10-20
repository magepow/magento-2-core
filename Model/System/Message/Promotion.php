<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magepow\Core\Helper\Data as CoreHelper;

/**
 * Admin system message: promotion notice.
 *
 * The message content comes from configuration (default in etc/config.xml).
 * There is no matching field in the admin UI, so merchants cannot change it.
 */
class Promotion implements MessageInterface
{
    /**
     * Fallback used only when the config value is empty.
     */
    public const FALLBACK_MESSAGE = 'Discover new Magepow extensions with special offers for your store.';
    public const FALLBACK_URL = 'https://magepow.com/magento-2-extensions.html';

    /**
     * @var CoreHelper
     */
    private $helper;

    /**
     * @param CoreHelper $helper
     */
    public function __construct(CoreHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity()
    {
        return md5('MAGEPOW_CORE_PROMO_' . $this->getMessage());
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed()
    {
        if (!$this->helper->isNotificationEnabled('promotion')) {
            return false;
        }
        $message = trim($this->getMessage());
        if (!$message) {
            return false;
        }
        // An empty date range means the promotion is always visible.
        $fromDate = trim((string)$this->helper->getNotificationConfig('promotion_from_date'));
        $toDate = trim((string)$this->helper->getNotificationConfig('promotion_to_date'));
        $currentTime = time();
        if ($fromDate && strtotime($fromDate) > $currentTime) {
            return false;
        }
        if ($toDate && strtotime($toDate) < $currentTime) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        $text = __('Magepow Promotion: %1', $this->getMessage());
        $url = $this->getUrl();
        if ($url) {
            $text .= ' ' . __('<a href="%1" target="_blank">View details</a>.', $url);
        }
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function getSeverity()
    {
        return (int)$this->helper->getNotificationConfig('promotion_severity') ?: self::SEVERITY_NOTICE;
    }

    /**
     * Retrieve the promotion message from configuration.
     *
     * @return string
     */
    private function getMessage(): string
    {
        $message = trim((string)$this->helper->getNotificationConfig('promotion_message'));
        return $message ?: self::FALLBACK_MESSAGE;
    }

    /**
     * Retrieve the promotion URL from configuration.
     *
     * @return string
     */
    private function getUrl(): string
    {
        $url = trim((string)$this->helper->getNotificationConfig('promotion_url'));
        return $url ?: self::FALLBACK_URL;
    }
}
