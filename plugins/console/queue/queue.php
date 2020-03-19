<?php

use Doctrine\DBAL\DriverManager;
use FOF30\Container\Container;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Weble\JoomlaQueues\Locator\ReceiverLocator;

defined('_JEXEC') or die;

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgConsoleQueue extends CMSPlugin
{
    /** @var \Joomla\CMS\Application\CliApplication */
    protected $app;
    protected $autoloadLanguage = true;
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;
    /**
     * @var \Weble\JoomlaQueues\Admin\Container
     */
    private $container;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->containerBuilder = new ContainerBuilder();

        $this->container = Container::getInstance('com_queues', [], 'admin');
    }

    public function onGetConsoleCommands(Application $console)
    {
        $eventDispatcher = new EventDispatcher();

        PluginHelper::importPlugin('queue');
        $results = $this->app->triggerEvent('onGetQueueHandlers');

        $handlers = [];
        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $message => $pluginHandlers) {
                $handlers[$message] = array_merge($handlers[$message] ?? [], $pluginHandlers);
            }
        }

        $consumeCommand = new ConsumeMessagesCommand(
            $this->container->queue->bus(),
            new ReceiverLocator([
                //'sync'     => new SyncTransport($this->container->queue->bus()),
                'doctrine' => $this->container->queue->doctrineTransport()
            ]),
            $eventDispatcher,
            Log::createDelegatedLogger(),
            [
                //'sync',
                'doctrine'
            ]
        );

        $console->addCommands([
            $consumeCommand
        ]);
    }
}
