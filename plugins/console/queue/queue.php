<?php

use FOF30\Container\Container;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\DebugCommand;
use Symfony\Component\Messenger\Command\FailedMessagesRemoveCommand;
use Symfony\Component\Messenger\Command\FailedMessagesRetryCommand;
use Symfony\Component\Messenger\Command\FailedMessagesShowCommand;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnSigtermSignalListener;
use Weble\JoomlaQueues\Command\PingQueueCommand;
use Weble\JoomlaQueues\Command\ThrowErrorCommand;
use Weble\JoomlaQueues\Transport\RetryStrategyLocator;
use Weble\JoomlaQueues\Transport\TransportLocator;

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
        $transportLocator = new TransportLocator();
        $retryStrategyLocator = new RetryStrategyLocator($transportLocator);
        $stopWorkerCache = new FilesystemAdapter('', 0, JPATH_CACHE . '/com_queues_stop_workers');
        $logger = Log::createDelegatedLogger();

        $failureTransportName = ComponentHelper::getParams('com_queues')->get('failure_transport', 'failure');

        /** @var \Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport $failureTransport */
        $failureTransport = $transportLocator->get($failureTransportName);

        // Attach Listeners to events
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(
            new SendFailedMessageToFailureTransportListener($failureTransport)
        );
        $dispatcher->addSubscriber(
            new SendFailedMessageForRetryListener($transportLocator, $retryStrategyLocator)
        );
        $dispatcher->addSubscriber(
            new StopWorkerOnRestartSignalListener($stopWorkerCache)
        );
        $dispatcher->addSubscriber(
            new StopWorkerOnSigtermSignalListener()
        );
        $dispatcher->addSubscriber(
            new DispatchPcntlSignalListener()
        );

        $consumeCommand = new ConsumeMessagesCommand(
            $this->container->bus->routableBus(),
            $transportLocator,
            $dispatcher,
            $logger,
            $transportLocator->getReceivers()
        );


        $console->addCommands([
            $consumeCommand,
            new DebugCommand($this->container->queue->handlersLocator()->debugHandlers()),
            new StopWorkersCommand($stopWorkerCache),
            new FailedMessagesShowCommand(
                $failureTransportName,
                $failureTransport
            ),
            new FailedMessagesRetryCommand(
                $failureTransportName,
                $failureTransport,
                $this->container->bus->routableBus(),
                $dispatcher,
                $logger
            ),
            new FailedMessagesRemoveCommand(
                $failureTransportName,
                $failureTransport
            ),
            new PingQueueCommand(),
            new ThrowErrorCommand()
        ]);
    }
}
