<?php


namespace Weble\JoomlaQueues\Locator;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BusLocator implements ContainerInterface
{
    /**
     * @var MessageBusInterface[]
     */
    private $buses = [];

    public function __construct()
    {
        PluginHelper::importPlugin('queue');
        $results = Factory::getApplication()->triggerEvent('onGetQueueBuses');

        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $busName => $bus) {
                $this->buses[$busName] = $bus;
            }
        }
    }

    /**
     * @return MessageBusInterface[]
     */
    public function all(): array
    {
        return $this->buses;
    }

    public function get($id)
    {
        return $this->buses[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->buses[$id]);
    }
}
