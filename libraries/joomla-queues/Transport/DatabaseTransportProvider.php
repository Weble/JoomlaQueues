<?php


namespace Weble\JoomlaQueues\Transport;


use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DatabaseTransportProvider extends TransportProvider
{
    protected $name = 'database';
    protected $tableName = 'queues_jobs';
    protected $queueName;

    public function __construct(string $queueName = 'default')
    {
        $this->queueName = $queueName;
    }

    public function getKey(): string
    {
        return $this->queueName;
    }

    public function transport(): TransportInterface
    {
        return $this->doctrineTransport();
    }

    public function doctrineTransport(): DoctrineTransport
    {
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
            'queue_name'        => $this->queueName
            // get setup on install
        ], $dbConnection);

        return new DoctrineTransport($driverConnection, $this->serializer());
    }
}
