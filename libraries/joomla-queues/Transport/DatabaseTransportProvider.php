<?php


namespace Weble\JoomlaQueues\Transport;


use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Middleware\DoctrineCloseConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrinePingConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrineTransactionMiddleware;

class DatabaseTransportProvider extends TransportProvider
{
    protected $name = 'database';
    protected $tableName = 'queues_jobs';
    protected $queueName;
    private $dbConnection;

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

    protected function customMiddlewares(): array
    {
        return [
            new DoctrinePingConnectionMiddleware(
                $this->dbConnection()
            ),
            new DoctrineCloseConnectionMiddleware(
                $this->dbConnection()
            ),
            new DoctrineTransactionMiddleware(
                $this->dbConnection()
            )
        ];
    }

    public function doctrineTransport(): DoctrineTransport
    {
        $config = Factory::getConfig();

        $driverConnection = new Connection([
            'table_name'        => $config->get('dbprefix') . $this->tableName,
            'redeliver_timeout' => 3600,
            'auto_setup'        => true,
            'queue_name'        => $this->queueName
            // get setup on install
        ], $this->dbConnection());

        return new DoctrineTransport($driverConnection, $this->serializer());
    }

    private function dbConnection(): \Doctrine\DBAL\Connection
    {
        if (!$this->dbConnection) {
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
            $this->dbConnection = $dbConnection;
        }

        return $this->dbConnection;
    }
}
