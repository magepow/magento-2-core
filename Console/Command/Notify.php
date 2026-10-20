<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * CLI: php bin/magento magepow:notify --type=update|security|promotion|feed [--force]
 * - type=update|security|promotion: push one message into adminnotification_inbox.
 * - type=feed: pull the remote feed into the admin inbox.
 */
declare(strict_types=1);

namespace Magepow\Core\Console\Command;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Notification\MessageInterface;
use Magepow\Core\Model\FeedFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command: push a Magepow notification into the admin inbox.
 */
class Notify extends Command
{
    public const TYPE_UPDATE = 'update';
    public const TYPE_SECURITY = 'security';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_FEED = 'feed';

    public const COMMAND_NAME = 'magepow:notify';

    /**
     * @var InboxFactory
     */
    private $inboxFactory;

    /**
     * @var FeedFactory
     */
    private $feedFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param InboxFactory $inboxFactory
     * @param FeedFactory $feedFactory
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        InboxFactory $inboxFactory,
        FeedFactory $feedFactory,
        LoggerInterface $logger,
        ?string $name = null
    ) {
        $this->inboxFactory = $inboxFactory;
        $this->feedFactory = $feedFactory;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Push Magepow notification (update/security/promotion/feed) to admin inbox')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'update|security|promotion|feed', 'feed')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Title', '')
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'Description', '')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'URL', '')
            ->addOption(
                'severity',
                null,
                InputOption::VALUE_OPTIONAL,
                '1=critical,2=major,3=minor,4=notice',
                ''
            )
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force fetch feed ignoring frequency');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = strtolower((string)$input->getOption('type'));
        try {
            if ($type === self::TYPE_FEED) {
                $this->feedFactory->create()->checkUpdate((bool)$input->getOption('force'));
                $output->writeln('<info>Feed checked and pulled to admin inbox (if any).</info>');
                return Cli::RETURN_SUCCESS;
            }

            $severityMap = [
                self::TYPE_UPDATE => MessageInterface::SEVERITY_MAJOR,
                self::TYPE_SECURITY => MessageInterface::SEVERITY_CRITICAL,
                self::TYPE_PROMOTION => MessageInterface::SEVERITY_NOTICE,
            ];
            if (!isset($severityMap[$type])) {
                $output->writeln('<error>Invalid --type. Use update|security|promotion|feed</error>');
                return Cli::RETURN_FAILURE;
            }
            $defaultTitles = [
                self::TYPE_UPDATE => 'Magepow update available',
                self::TYPE_SECURITY => 'Magepow security notice',
                self::TYPE_PROMOTION => 'Magepow promotion',
            ];
            $severity = (int)$input->getOption('severity') ?: $severityMap[$type];
            $title = (string)$input->getOption('title') ?: $defaultTitles[$type];
            $description = (string)$input->getOption('message') ?: $title;
            $url = (string)$input->getOption('url') ?: 'https://magepow.com/';

            $this->inboxFactory->create()->add($severity, $title, $description, $url);
            $output->writeln('<info>Pushed [' . $type . '] notification to admin inbox.</info>');
            return Cli::RETURN_SUCCESS;
        } catch (\Throwable $exception) {
            $this->logger->warning('Magepow_Core notify command error: ' . $exception->getMessage());
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
