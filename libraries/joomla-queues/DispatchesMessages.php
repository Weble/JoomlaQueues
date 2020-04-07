<?php

namespace Weble\JoomlaQueues;

use FOF30\Container\Container;

trait DispatchesMessages
{
    protected function dispatchMessage($message)
    {
        Container::getInstance('com_queues', [], 'admin')->queue->dispatch(
            $message
        );
    }
}
