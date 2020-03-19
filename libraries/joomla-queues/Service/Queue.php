<?php

namespace Weble\JoomlaQueues\Service;

use Joomla\CMS\Plugin\PluginHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Locator\SenderLocator;

class Queue
{
    /** @var  Container  The container we belong to */
    protected $container = null;
    /**
     * @var MessageBus
     */
    private $bus;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function bus()
    {
        if (!$this->bus) {
            $eventDispatcher = new EventDispatcher();

            PluginHelper::importPlugin('queue');
            $this->container->platform->importPlugin('queue');
            $results = $this->container->platform->runPlugins('onGetQueueHandlers', []);

            $handlers = [];
            foreach ($results as $pluginIndex => $pluginHandlerList) {
                foreach ($pluginHandlerList as $message => $pluginHandlers) {
                    $handlers[$message] = array_merge($handlers[$message] ?? [], $pluginHandlers);
                }
            }

            $this->bus = new RoutableMessageBus(new ContainerBuilder(), new MessageBus([
                new SendMessageMiddleware(
                    new SenderLocator([
                        SyncTransport::class
                    ]),
                    $eventDispatcher
                ),
                new HandleMessageMiddleware(new HandlersLocator([
                    $handlers
                ])),
            ]));
        }

        return $this->bus;
    }
}
