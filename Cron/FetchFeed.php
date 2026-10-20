<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Cron;

use Magepow\Core\Model\FeedFactory;
use Psr\Log\LoggerInterface;

/**
 * Cron job: refresh the admin notification feed.
 */
class FetchFeed
{
    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FeedFactory $feedFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeedFactory $feedFactory,
        LoggerInterface $logger
    ) {
        $this->feedFactory = $feedFactory;
        $this->logger = $logger;
    }

    /**
     * Fetch the remote feed and import new items into the admin inbox.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->feedFactory->create()->checkUpdate();
        } catch (\Throwable $exception) {
            $this->logger->warning('Magepow_Core feed cron error: ' . $exception->getMessage());
        }
    }
}
