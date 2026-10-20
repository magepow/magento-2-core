<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Observer;

use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magepow\Core\Model\FeedFactory;
use Psr\Log\LoggerInterface;

/**
 * Refresh the notification feed on every admin page load.
 *
 * Follows the same approach as Magento_AdminNotification: the feed model
 * itself enforces the check frequency, so this observer stays lightweight.
 */
class CheckNotification implements ObserverInterface
{
    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FeedFactory $feedFactory
     * @param BackendSession $backendSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeedFactory $feedFactory,
        BackendSession $backendSession,
        LoggerInterface $logger
    ) {
        $this->feedFactory = $feedFactory;
        $this->backendSession = $backendSession;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->backendSession->isLoggedIn()) {
            return;
        }
        try {
            $this->feedFactory->create()->checkUpdate();
        } catch (\Throwable $exception) {
            $this->logger->warning('Magepow_Core feed observer error: ' . $exception->getMessage());
        }
    }
}
