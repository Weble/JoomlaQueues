<?php

namespace Weble\JoomlaQueues\Bus;

use Joomla\Registry\Registry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BusLocator implements ContainerInterface
{
    /**
     * @var BusProvider[]
     */
    private $buses;

    public function __construct(Registry $buses)
    {
        $this->buses = $buses;
    }

    /**
     * @return MessageBusInterface[]
     */
    public function getBuses(): array
    {
        $buses = [];
        foreach ($this->buses as $busProvider) {
            $buses[$busProvider->getKey()] = $busProvider->bus();
        }

        return $buses;
    }

    /**
     * @param string $id
     * @return MessageBusInterface|null
     */
    public function get($id): ?MessageBusInterface
    {
        return $this->has($id) ? $this->buses->get($id)->bus() : null ;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return $this->buses->exists($id);
    }
}
