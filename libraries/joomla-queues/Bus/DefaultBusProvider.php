<?php


namespace Weble\JoomlaQueues\Bus;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Middleware\DoctrineCloseConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrinePingConnectionMiddleware;
use Weble\JoomlaQueues\Middleware\DoctrineTransactionMiddleware;
use Weble\JoomlaQueues\Transport\HasDoctrineConnection;

class DefaultBusProvider extends BusProvider
{
    use HasDoctrineConnection;

    protected $name = 'default';

    /**
     * @return TraceableMessageBus
     */
    public function bus(): MessageBusInterface
    {
        return new TraceableMessageBus(new MessageBus(
            $this->middlewares()
        ));
    }

    /**
     * @return MiddlewareInterface[]
     */
    protected function customMiddlewares(): array
    {
        return [
           new DoctrineTransactionMiddleware($this->dbConnection()),
           new DoctrinePingConnectionMiddleware($this->dbConnection()),
           new DoctrineCloseConnectionMiddleware($this->dbConnection()),
        ];
    }
}
