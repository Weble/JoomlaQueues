<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Handler\HandlersLocator;

class Queue
{
    /** @var  Container  The container we belong to */
    protected $container = null;
    /**
     * @var HandlersLocator
     */
    private $handlersLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->handlersLocator = new HandlersLocator();
    }

    /**
     * @param @param object|Envelope $message
     * @throws \Throwable
     */
    public function dispatch($message, $busId = null)
    {
        $this->container->bus->getBus($busId)->dispatch($message);
    }

    public function handlersLocator(): HandlersLocator
    {
        return $this->handlersLocator;
    }
}
