<?php

namespace Weble\JoomlaQueues\Service;

use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Plugin\PluginHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Bus\JoomlaMessageBus;
use Weble\JoomlaQueues\Locator\SenderLocator;

class Queue
{
    /** @var  Container  The container we belong to */
    protected $container = null;
    /**
     * @var MessageBus
     */
    private $bus;
    /**
     * @var DoctrineTransport
     */
    private $doctrineTransport;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function bus()
    {
        if (!$this->bus) {
            PluginHelper::importPlugin('queue');
            $this->container->platform->importPlugin('queue');
            $results = $this->container->platform->runPlugins('onGetQueueHandlers', []);

            $handlers = [];
            foreach ($results as $pluginIndex => $pluginHandlerList) {
                foreach ($pluginHandlerList as $message => $pluginHandlers) {
                    $handlers[$message] = array_merge($handlers[$message] ?? [], $pluginHandlers);
                }
            }

            $this->bus = new JoomlaMessageBus(new MessageBus([
                new SendMessageMiddleware(
                    new SenderLocator([
                        //SyncTransport::class,
                        '*' => [
                            DoctrineTransport::class
                        ]
                    ], [
                        DoctrineTransport::class => $this->doctrineTransport()
                    ]),
                    new EventDispatcher()
                ),
                new HandleMessageMiddleware(new HandlersLocator($handlers)),
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
}
