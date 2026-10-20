<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Controller\Adminhtml\Notification;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magepow\Core\Model\FeedFactory;
use Psr\Log\LoggerInterface;

/**
 * Admin controller: manually refresh the notification feed.
 *
 * Used by the "Fetch Now" button on the module configuration page.
 */
class Fetch extends Action
{
    public const ADMIN_RESOURCE = 'Magepow_Core::config';

    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FeedFactory $feedFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FeedFactory $feedFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->feedFactory = $feedFactory;
        $this->logger = $logger;
    }

    /**
     * Refresh the feed and redirect back to the configuration page.
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $this->feedFactory->create()->checkUpdate(true);
            $this->messageManager->addSuccessMessage(
                __('Magepow feed fetched. Check System Notifications (bell icon).')
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('Magepow_Core manual feed fetch error: ' . $exception->getMessage());
            $this->messageManager->addErrorMessage(__('Feed fetch failed. Please try again later.'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/system_config/edit', ['section' => 'magepow_core']);
    }
}
