<?php

namespace Weble\JoomlaQueues\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Locator\BusLocator;

class RoutableBus
{
    /** @var  Container  The container we belong to */
    private $container;
    /**
     * @var RoutableMessageBus
     */
    private $bus;
    /**
     * @var BusLocator
     */
    private $busLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->busLocator = new BusLocator($container);
    }

    public function bus(): RoutableMessageBus
    {
        if (!$this->bus) {
            $this->bus = new RoutableMessageBus(
                $this->busLocator,
                $this->container->defaultBus->bus()
            );
        }

        return $this->bus;
    }
}
