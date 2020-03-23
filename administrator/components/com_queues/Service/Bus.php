<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Bus\BusLocator;
use Weble\JoomlaQueues\Bus\ProvidesBus;

class Bus
{
    /** @var  Container */
    private $container;
    /**
     * @var RoutableMessageBus
     */
    private $routableBus;
    /**
     * @var string
     */
    private $defaultBusName = 'default';
    /**
     * @var BusLocator
     */
    private $busLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->busLocator = new BusLocator();
        $this->routableBus = new RoutableMessageBus(
            $this->busLocator,
            $this->defaultBus()
        );
    }

    /**
     * @return ProvidesBus[]
     */
    public function getBuses(): array
    {
        return $this->busLocator->all();
    }

    public function getBusIds(): array
    {
        return array_keys($this->getBuses());
    }

    public function getBus(?string $busId = null): MessageBusInterface
    {
        if (!$busId) {
            $busId = $this->getDefaultName();
        }

        return $this->busLocator->get($busId);
    }

    public function getDefaultName(): string
    {
        return $this->defaultBusName;
    }

    public function defaultBus(): MessageBusInterface
    {
        return $this->getBus($this->getDefaultName());
    }

    public function routableBus(): RoutableMessageBus
    {
        return $this->routableBus;
    }
}
