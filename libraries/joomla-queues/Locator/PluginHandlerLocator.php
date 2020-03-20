<?php


namespace Weble\JoomlaQueues\Locator;


use FOF30\Container\Container;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;

class PluginHandlerLocator extends HandlersLocator
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container, array $handlers = [])
    {
        $this->container = $container;

        PluginHelper::importPlugin('queue');
        $results = Factory::$application->triggerEvent('onGetQueueHandlers', []);

        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $message => $pluginHandlers) {
                $handlerList = array_merge($handlers[$message] ?? [], $pluginHandlers);
                $handlers[$message] = $handlerList;
            }
        }

        parent::__construct($handlers);
    }

}
