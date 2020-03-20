<?php


namespace Weble\JoomlaQueues\Handler;

use FOF30\Container\Container;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Weble\JoomlaQueues\Message\PingMessage;

class PingHandler implements MessageHandlerInterface, MessageSubscriberInterface
{
    public function __invoke(PingMessage $message)
    {
        echo "Pong\n: ";
    }

    public static function getHandledMessages(): iterable
    {
        $transport = Container::getInstance('com_queues')->transport->getTransportKeys()[0] ?? 'default';

        yield PingMessage::class => [
            //'from_transport' => $transport
        ];
    }
}
