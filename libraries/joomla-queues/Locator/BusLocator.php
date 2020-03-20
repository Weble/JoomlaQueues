<?php


namespace Weble\JoomlaQueues\Locator;


use FOF30\Container\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class BusLocator implements ContainerInterface
{
    /**
     * @var MessageBusInterface[]
     */
    private $buses = [];
    /**
     * @var \Weble\JoomlaQueues\Admin\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->buses[$this->container->defaultBus->getName()] = $this->container->defaultBus->bus();

        $this->container->platform->importPlugin('queue');
        $results = $this->container->platform->runPlugins('onGetQueueBuses', []);

        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $busName => $bus) {
                $this->buses[$busName] = $bus;
            }
        }
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
