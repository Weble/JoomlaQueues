<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Locator\PluginHandlerLocator;

class Queue
{
    /** @var  Container  The container we belong to */
    protected $container = null;
    /**
     * @var PluginHandlerLocator
     */
    private $handlersLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->handlersLocator = new PluginHandlerLocator();
    }

    /**
     * @param @param object|Envelope $message
     * @throws \Throwable
     */
    public function dispatch($message, $busId = null)
    {
        $this->container->bus->getBus($busId)->dispatch($message);
    }

    public function handlersLocator(): PluginHandlerLocator
    {
        return $this->handlersLocator;
    }
}
