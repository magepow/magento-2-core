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
 * Admin system message: security notice.
 *
 * The message content comes from configuration (default in etc/config.xml).
 * There is no matching field in the admin UI, so merchants cannot change it.
 */
class Security implements MessageInterface
{
    /**
     * Fallback used only when the config value is empty.
     */
    public const FALLBACK_MESSAGE = 'A security update is available for Magepow extensions.'
        . ' Please update immediately to protect your store.';
    public const FALLBACK_URL = 'https://magepow.com/security.html';

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
        return md5('MAGEPOW_CORE_SECURITY_' . $this->getMessage());
    }

    /**
     * @inheritdoc
     */
    public function isDisplayed()
    {
        if (!$this->helper->isNotificationEnabled('security')) {
            return false;
        }
        return (bool)trim($this->getMessage());
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        $text = __('Magepow Security Notice: %1', $this->getMessage());
        $url = $this->getUrl();
        if ($url) {
            $text .= ' ' . __('<a href="%1" target="_blank">Learn more</a>.', $url);
        }
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function getSeverity()
    {
        return (int)$this->helper->getNotificationConfig('security/severity') ?: self::SEVERITY_CRITICAL;
    }

    /**
     * Retrieve the notice message from configuration.
     *
     * @return string
     */
    private function getMessage(): string
    {
        $message = trim((string)$this->helper->getNotificationConfig('security/message'));
        return $message ?: self::FALLBACK_MESSAGE;
    }

    /**
     * Retrieve the notice URL from configuration.
     *
     * @return string
     */
    private function getUrl(): string
    {
        $url = trim((string)$this->helper->getNotificationConfig('security/url'));
        return $url ?: self::FALLBACK_URL;
    }
}
