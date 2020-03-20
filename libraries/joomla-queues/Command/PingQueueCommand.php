<?php

namespace Weble\JoomlaQueues\Command;

use FOF30\Container\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Weble\JoomlaQueues\Message\PingMessage;

class PingQueueCommand extends Command
{
    protected static $defaultName = 'queue:ping';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Weble\JoomlaQueues\Admin\Container $container */
        $container = Container::getInstance('com_queues', [], 'admin');
        $container->queue->dispatch(new PingMessage());

        return 0;
    }
}
