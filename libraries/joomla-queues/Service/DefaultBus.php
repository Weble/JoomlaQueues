<?php

namespace Weble\JoomlaQueues\Service;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Locator\BusLocator;
use Weble\JoomlaQueues\Locator\PluginHandlerLocator;
use Weble\JoomlaQueues\Locator\SenderLocator;

class DefaultBus
{
    /** @var  Container  */
    private $container = null;
    /**
     * @var TraceableMessageBus
     */
    private $bus;
    /**
     * @var DoctrineTransport
     */
    private $doctrineTransport;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var PluginHandlerLocator
     */
    private $handlersLocator;
    /**
     * @var string
     */
    private $defaultBusName = 'messenger.bus.default';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return $this->defaultBusName;
    }

    public function bus(): TraceableMessageBus
    {
        if (!$this->bus) {
            $this->bus = new TraceableMessageBus(new MessageBus([
                new AddBusNameStampMiddleware($this->defaultBusName),
                new DispatchAfterCurrentBusMiddleware(),
                new FailedMessageProcessingMiddleware(),
                new SendMessageMiddleware(
                    new SenderLocator([
                        //SyncTransport::class,
                        '*' => [
                            DoctrineTransport::class
                        ]
                    ], [
                        DoctrineTransport::class => $this->doctrineTransport()
                    ]),
                    $this->eventDispatcher()
                ),
                new HandleMessageMiddleware($this->handlersLocator()),
            ]));
        }

        return $this->bus;
    }

    public function doctrineTransport(): DoctrineTransport
    {
        if (!$this->doctrineTransport) {
            $config = $this->container->platform->getConfig();

            $connectionParams = array(
                'dbname'   => $config->get('db'),
                'user'     => $config->get('user'),
                'password' => $config->get('password'),
                'host'     => $config->get('host'),
                'driver'   => 'pdo_mysql',
            );

            $dbConnection = DriverManager::getConnection($connectionParams);
            $dbConnection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

            $driverConnection = new Connection([
                'table_name'        => $config->get('dbprefix') . 'queues_jobs',
                'redeliver_timeout' => 3600,
                'auto_setup'        => false,
            ], $dbConnection);

            $this->doctrineTransport = new DoctrineTransport($driverConnection, new PhpSerializer());
        }

        return $this->doctrineTransport;

    }

    public function eventDispatcher(): EventDispatcher
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }
        return $this->eventDispatcher;
    }


    public function handlersLocator(): HandlersLocatorInterface
    {
        if (!$this->handlersLocator) {
            $this->handlersLocator = new PluginHandlerLocator($this->container);
        }

        return $this->handlersLocator;
    }
}
