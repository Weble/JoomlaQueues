<?php

use FOF30\Container\Container;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
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
        $consumeCommand = new ConsumeMessagesCommand(
            $this->container->routableBus->bus(),
            new ReceiverLocator([
                // 'sync'     => new SyncTransport($this->container->queue->bus()),
                'doctrine' => $this->container->defaultBus->doctrineTransport()
            ]),
            $this->container->defaultBus->eventDispatcher(),
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
