<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
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
        try {
            $this->container->bus->getBus($busId)->dispatch($message);
        } catch (HandlerFailedException $e) {
            throw $e->getPrevious();
        }
    }
}
