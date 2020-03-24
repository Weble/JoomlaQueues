<?php


namespace Weble\JoomlaQueues\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Admin\Model\Jobs;
use Weble\JoomlaQueues\Stamp\LastFailedTimeStamp;


class LogFailedMessageListener implements EventSubscriberInterface
{
    private $failureSender;
    private $container;

    public function __construct(Container $container, SenderInterface $failureSender)
    {
        $this->failureSender = $failureSender;
        $this->container = $container;
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event)
    {
        $envelope = $event->getEnvelope();

        $messageIdStamp = $envelope->last(TransportMessageIdStamp::class);
        $messageId = $messageIdStamp ? $messageIdStamp->getId() : null;
        $stamp = new LastFailedTimeStamp($messageId);
        if (null !== $envelope->last(LastFailedTimeStamp::class)) {
            $stamp = new LastFailedTimeStamp($envelope->last(LastFailedTimeStamp::class)->getOriginalMessageId());
        }

        /** @var Jobs $model */
        $model = $this->container->factory->model('Jobs')->tmpInstance();
        $model->fromEnvelope($envelope->with($stamp))->save();

        return $envelope;
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => [
                'onMessageFailed',
                -100
            ],
        ];
    }
}

