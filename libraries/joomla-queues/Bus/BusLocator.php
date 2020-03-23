<?php

namespace Weble\JoomlaQueues\Bus;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BusLocator implements ContainerInterface
{
    /**
     * @var BusProvider[]
     */
    private $buses = [];

    public function __construct()
    {
        PluginHelper::importPlugin('queue');
        $results = Factory::getApplication()->triggerEvent('onGetQueueBuses');

        /**
         * @var $busProviders BusProvider[]
         */
        foreach ($results as $pluginIndex => $busProviders) {
            /**
             * @var $bus BusProvider
             */
            foreach ($busProviders as $busName => $bus) {
                $this->buses[$bus->getKey()] = $bus;
            }
        }
    }

    /**
     * @return BusProvider[]
     */
    public function all(): array
    {
        return $this->buses;
    }

    /**
     * @param string $id
     * @return BusProvider|null
     */
    public function getProvider($id): ?BusProvider
    {
        return $this->buses[$id] ?? null;
    }

    /**
     * @param string $id
     * @return MessageBusInterface|null
     */
    public function get($id): ?MessageBusInterface
    {
        return $this->buses[$id] ? $this->buses[$id]->bus() : null;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->buses[$id]);
    }
}
