<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Symfony\Component\Messenger\Handler\HandlersLocator;
use Weble\JoomlaQueues\Admin\Container;

class Handler
{
    /** @var  Container */
    private $container;

    /**
     * @var HandlersLocator
     */
    private $handlersLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->handlersLocator = new HandlersLocator(
            (array) $this->container->queueConfig->messageHandlers()->toObject()
        );
    }

    public function locator(): HandlersLocator
    {
        return $this->handlersLocator;
    }
}
