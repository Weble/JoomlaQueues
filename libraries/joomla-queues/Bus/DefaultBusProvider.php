<?php


namespace Weble\JoomlaQueues\Bus;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\TraceableMessageBus;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DefaultBusProvider extends BusProvider
{
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
}
