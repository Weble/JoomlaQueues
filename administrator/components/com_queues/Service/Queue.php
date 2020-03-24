<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Weble\JoomlaQueues\Admin\Container;

class Queue
{
    /** @var  Container  The container we belong to */
    protected $container = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param @param object|Envelope $message
     * @throws \Throwable
     */
    public function dispatch($message, $busId = null)
    {
        $this->container->bus->getBus($busId)->dispatch($message);
    }
}
