<?php


namespace Weble\JoomlaQueues\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Admin\Model\Jobs;
use Weble\JoomlaQueues\Stamp\HandledTimeStamp;
use Weble\JoomlaQueues\Stamp\LastFailedTimeStamp;
use Weble\JoomlaQueues\Stamp\ReceivedTimeStamp;
use Weble\JoomlaQueues\Stamp\SentTimeStamp;

class LogHandledMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {

        /** @var Container $container */
        $container = Container::getInstance('com_queues', [], 'admin');

        /** @var Jobs $model */
        $model = $container->factory->model('Jobs')->tmpInstance();
        $model->fromEnvelope($envelope)->save();

        return $stack->next()->handle($envelope, $stack);
    }

}
