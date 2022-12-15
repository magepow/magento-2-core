<?php
/**
 * @Author: nguyen
 * @Date:   2020-12-15 14:01:01
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2022-12-15 09:25:12
 */

namespace Magepow\Core\Console\Command;

use Symfony\Component\Console\Command\Command; // for parent class
use Symfony\Component\Console\Input\InputInterface; // for InputInterface used in execute method
use Symfony\Component\Console\Output\OutputInterface; // for OutputInterface used in execute method
use Symfony\Component\Filesystem\Filesystem;

class CleanUp extends Command
{

    private $dirs = [
        // 'generated',
        'var/log',
        'var/report',
        'var/session'
    ];

    private $tables = [
        'adminnotification_inbox',
        'admin_passwords',
        'admin_user_session',
        'captcha_log',
        'customer_visitor',
        'customer_log',
        'cron_schedule',
        'report_event',
        'report_viewed_product_index',
    ];

    protected function configure()
    {
        // bin/magento cleanUp
        $this->setName('cleanUp')
             ->setDescription('Clear TMP Tables');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
        $fs = new Filesystem();
        try {
            foreach ($this->tables as $table) {
                $connection->truncateTable($table);
                $output->writeln("TRUNCATE $table");
            }
            foreach ($this->dirs as $dir) {
                if($fs->exists($dir)){
                    $fs->remove(array($dir));
                    $output->writeln("Cleared $dir");
                }else {
                    $output->writeln("Can\'t find directy $dir");
                }
            }
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while deleting your directory at " . $e->getPath();
        }
    }
}
