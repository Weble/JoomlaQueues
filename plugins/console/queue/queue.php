<?php

use FOF30\Container\Container;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\DebugCommand;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Weble\JoomlaQueues\Command\PingQueueCommand;
use Weble\JoomlaQueues\Locator\PluginTransportLocator;

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
     * @var \Weble\JoomlaQueues\Admin\Container
     */
    private $container;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->container = Container::getInstance('com_queues', [], 'admin');
    }

    public function onGetConsoleCommands(Application $console)
    {
        $transportLocator = new PluginTransportLocator();

        $consumeCommand = new ConsumeMessagesCommand(
            $this->container->bus->routableBus(),
            $transportLocator,
            new EventDispatcher(),
            Log::createDelegatedLogger(),
            $transportLocator->getReceivers()
        );


        $cache = new FilesystemAdapter();

        $console->addCommands([
            $consumeCommand,
            new DebugCommand($this->container->queue->handlersLocator()->debugHandlers()),
            new StopWorkersCommand($cache),
            new PingQueueCommand()
        ]);
    }
}
