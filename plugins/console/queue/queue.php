<?php

use FOF30\Container\Container;
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
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Command\PingQueueCommand;
use Weble\JoomlaQueues\Command\ThrowErrorCommand;

require_once(JPATH_LIBRARIES . '/joomla-queues/bootstrap.php');

class PlgConsoleQueue extends CMSPlugin
{
    /** @var \Joomla\CMS\Application\CliApplication */
    protected $app;
    /** @var bool */
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
        $stopWorkerCache = new FilesystemAdapter('', 0, JPATH_CACHE . '/com_queues_stop_workers');
        $logger = Log::createDelegatedLogger();
        $dispatcher = $this->createEventDispatcher($stopWorkerCache);

        $console->addCommands([
            new ConsumeMessagesCommand(
                $this->container->bus->routableBus(),
                $this->container->transport->locator(),
                $dispatcher,
                $logger,
                $this->container->transport->locator()->getReceivers()
            ),
            new DebugCommand($this->container->queue->handlersLocator()->debugHandlers()),
            new StopWorkersCommand($stopWorkerCache),
            new FailedMessagesShowCommand(
                $this->container->transport->failureTransportName(),
                $this->container->transport->failureTransport()
            ),
            new FailedMessagesRetryCommand(
                $this->container->transport->failureTransportName(),
                $this->container->transport->failureTransport(),
                $this->container->bus->routableBus(),
                $dispatcher,
                $logger
            ),
            new FailedMessagesRemoveCommand(
                $this->container->transport->failureTransportName(),
                $this->container->transport->failureTransport()
            ),
            new PingQueueCommand(),
            new ThrowErrorCommand()
        ]);
    }

    private function createEventDispatcher(FilesystemAdapter $stopWorkerCache): EventDispatcher
    {
        // Attach Listeners to events
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(
            new SendFailedMessageToFailureTransportListener($this->container->transport->failureTransport())
        );
        $dispatcher->addSubscriber(
            new SendFailedMessageForRetryListener(
                $this->container->transport->locator(),
                $this->container->transport->retryStrategyLocator()
            )
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
        return $dispatcher;
    }
}
