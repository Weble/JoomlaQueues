<?php


namespace Weble\JoomlaQueues\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Admin\Model\Jobs;

class LogHandledMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var Container $container */
        $container = Container::getInstance('com_queues', [], 'admin');

        /** @var Jobs $model */
        $model = $container->factory->model('Jobs')->tmpInstance();

        /** @var TransportMessageIdStamp $messageIdStamp */
        $messageIdStamp = $envelope->last(TransportMessageIdStamp::class);
        /** @var BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        /** @var ReceivedStamp $receivedStamp */
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        /** @var ReceivedStamp $consumed */
        $consumed = $envelope->last(ReceivedStamp::class);

        $data = (new Serializer())->encode($envelope);

        $model->save([
            'message_id' => $messageIdStamp ? $messageIdStamp->getId() : null,
            'bus' => $busNameStamp ? $busNameStamp->getBusName() : null,
            'transport' => $receivedStamp ? $receivedStamp->getTransportName() : null,
            'headers' => json_encode($data['headers'] ?? null),
            'received_on' => '',
            'consumed_on' => '',
            'handled_on' => '',
            'body' => $data['body'] ?? null,
        ]);

        return $stack->next()->handle($envelope, $stack);
    }

}
