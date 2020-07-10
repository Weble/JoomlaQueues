<?php


namespace Weble\JoomlaQueues\Transport;


use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Middleware\DoctrineCloseConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrinePingConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrineTransactionMiddleware;

class DatabaseTransportProvider extends TransportProvider
{
    use HasDoctrineConnection;

    protected $name = 'database';
    protected $tableName = 'queues_messages';
    protected $queueName;
    protected $params;

    public function __construct(string $queueName = 'default', Registry $params = null)
    {
        $this->queueName = $queueName;
        $this->params = $params;
    }

    public function getKey(): string
    {
        return $this->queueName;
    }

    public function transport(): TransportInterface
    {
        return $this->doctrineTransport();
    }

    public function retryStrategy(): RetryStrategyInterface
    {
        if (!$this->params) {
            return parent::retryStrategy();
        }

        if (!$this->params->get('override_retry_strategy', 0)) {
            return parent::retryStrategy();
        }

        return $this->useMultiplierRetryStrategy($this->params);
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


}
