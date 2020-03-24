<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Weble\JoomlaQueues\Stamp\HandledTimeStamp;
use Weble\JoomlaQueues\Stamp\LastFailedTimeStamp;
use Weble\JoomlaQueues\Stamp\ReceivedTimeStamp;
use Weble\JoomlaQueues\Stamp\SentTimeStamp;

class Jobs extends DataModel
{
    public function __construct(Container $container, array $config = array())
    {
        parent::__construct($container, $config);

        $this->addBehaviour('Filters');
    }

    public function fromEnvelope(Envelope $envelope): self
    {
        /** @var TransportMessageIdStamp $messageIdStamp */
        $messageIdStamp = $envelope->last(TransportMessageIdStamp::class);
        /** @var BusNameStamp $busNameStamp */
        $busNameStamp = $envelope->last(BusNameStamp::class);
        /** @var ReceivedStamp $receivedStamp */
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        /** @var ReceivedTimeStamp $receivedStamp */
        $receivedTimeStamp = $envelope->last(ReceivedTimeStamp::class);
        /** @var SentTimeStamp $receivedStamp */
        $sentTimeStamp = $envelope->last(SentTimeStamp::class);
        /** @var HandledTimeStamp $handledTimeStamp */
        $handledTimeStamp = $envelope->last(HandledTimeStamp::class);
        /** @var LastFailedTimeStamp $lastFailedTimeStamp */
        $lastFailedTimeStamp = $envelope->last(LastFailedTimeStamp::class);

        $data = (new Serializer())->encode($envelope);

        $originalMessageId = $lastFailedTimeStamp ? $lastFailedTimeStamp->getOriginalMessageId() : null;
        $messageId = $originalMessageId ?: ($messageIdStamp ? $messageIdStamp->getId() : null);
        $transportName = $receivedStamp ? $receivedStamp->getTransportName() : null;
        if ($messageId && $transportName) {
            $this->find([
                'message_id' => $messageId,
                'transport' => $transportName,
            ]);
        }

        $this->bind([
            'bus' => $busNameStamp ? $busNameStamp->getBusName() : null,
            'message_id' => $messageId,
            'transport' => $transportName,
            'headers' => json_encode($data['headers'] ?? null),
            'sent_on' => $sentTimeStamp ? $sentTimeStamp->getTime()->toSql() : null,
            'received_on' => $receivedTimeStamp ? $receivedTimeStamp->getTime()->toSql() : null,
            'handled_on' => $handledTimeStamp ? $handledTimeStamp->getTime()->toSql() : null,
            'last_failed_on' => $lastFailedTimeStamp ? $lastFailedTimeStamp->getTime()->toSql() : null,
            'body' => $data['body'] ?? null,
        ]);

        return $this;
    }
}
