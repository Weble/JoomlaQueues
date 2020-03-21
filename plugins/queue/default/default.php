<?php

use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\RejectRedeliveredMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Weble\JoomlaQueues\Locator\PluginHandlerLocator;
use Weble\JoomlaQueues\Locator\PluginTransportLocator;

defined('_JEXEC') or die;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgQueueDefault extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;
    protected static $transports = [];
    protected $tableName = 'queues_jobs';

    public function onGetQueueBuses()
    {
        $transportLocator = new PluginTransportLocator();

        $dispatcher = new EventDispatcher();

        $bus = new TraceableMessageBus(new MessageBus([
            new AddBusNameStampMiddleware('default'),
            new RejectRedeliveredMessageMiddleware(),
            new DispatchAfterCurrentBusMiddleware(),
            new FailedMessageProcessingMiddleware(),

            // Custom Middlewares should go here

            new SendMessageMiddleware(
                $transportLocator,
                $dispatcher
            ),
            new HandleMessageMiddleware(
                new PluginHandlerLocator()
            ),
        ]));

        return [
            'default' => $bus
        ];
    }

    public function onGetQueueTransports()
    {
        return [
            'database' => $this->doctrineTransport('default'),
            'failure'  => $this->doctrineTransport('failure')
        ];
    }

    public function doctrineTransport($queueName = 'default'): DoctrineTransport
    {
        if (!isset(self::$transports[$queueName])) {
            $config = Factory::getConfig();

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
                'table_name'        => $config->get('dbprefix') . $this->tableName,
                'redeliver_timeout' => 3600,
                'auto_setup'        => true,
                'queue_name'        => $queueName
                // get setup on install
            ], $dbConnection);

            self::$transports[$queueName] = new DoctrineTransport($driverConnection, new PhpSerializer());
        }

        return self::$transports[$queueName];

    }
}
