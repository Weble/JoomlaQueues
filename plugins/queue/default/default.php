<?php

use Doctrine\DBAL\DriverManager;
use FOF30\Container\Container;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
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
    protected static $transport;
    protected $tableName = 'queues_jobs';

    public function onGetQueueBuses()
    {
        $bus = new TraceableMessageBus(new MessageBus([
            new AddBusNameStampMiddleware('default'),
            new DispatchAfterCurrentBusMiddleware(),
            new FailedMessageProcessingMiddleware(),
            new SendMessageMiddleware(
                new PluginTransportLocator(),
                new EventDispatcher()
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
            'database' => $this->doctrineTransport()
        ];
    }

    public function doctrineTransport(): DoctrineTransport
    {
        if (!self::$transport) {
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
                'auto_setup'        => true, // get setup on install
            ], $dbConnection);

            self::$transport = new DoctrineTransport($driverConnection, new PhpSerializer());
        }

        return self::$transport;

    }
}
