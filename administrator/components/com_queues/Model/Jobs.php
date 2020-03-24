<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Weble\JoomlaQueues\Stamp\HandledTimeStamp;
use Weble\JoomlaQueues\Stamp\JobIdStamp;
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

    protected function onBeforeBuildQuery(\JDatabaseQuery &$query, $overrideLimits = false)
    {
        $this->setState('filter_order', $this->getState('filter_order', 'queues_job_id'));
        $this->setState('filter_order_Dir', $this->getState('filter_order_Dir', 'DESC'));

        if ($status = $this->getState('job_status')) {
            switch ($status) {
                case 'failed':
                    $this->whereRaw('last_failed_on IS NOT NULL');
                    $this->whereRaw('handled_on IS NULL');
                    break;
                case 'handled':
                    $this->whereRaw('handled_on IS NOT NULL');
                    break;
                case 'processing':
                    $this->whereRaw('received_on IS NOT NULL');
                    $this->whereRaw('last_failed_on IS NULL');
                    $this->whereRaw('handled_on IS NULL');
                    break;
                case 'waiting':
                    $this->whereRaw('sent_on IS NOT NULL');
                    $this->whereRaw('handled_on IS NULL');
                    $this->whereRaw('last_failed_on IS NULL');
                    $this->whereRaw('received_on IS NULL');
                    break;
            }
        }
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
        /** @var JobIdStamp $jobIdStamp */
        $jobIdStamp = $envelope->last(JobIdStamp::class);

        $data = (new Serializer())->encode($envelope);

        if ($jobIdStamp && $jobIdStamp->getJobId()) {
            $this->find($jobIdStamp->getJobId());
        }

        $this->bind([
            'bus' => $busNameStamp ? $busNameStamp->getBusName() : null,
            'message_id' => $messageIdStamp ? $messageIdStamp->getId() : null,
            'transport' => $receivedStamp ? $receivedStamp->getTransportName() : null,
            'headers' => json_encode($data['headers'] ?? null),
            'sent_on' => $sentTimeStamp ? $sentTimeStamp->getTime()->toSql() : null,
            'received_on' => $receivedTimeStamp ? $receivedTimeStamp->getTime()->toSql() : null,
            'handled_on' => $handledTimeStamp ? $handledTimeStamp->getTime()->toSql() : null,
            'last_failed_on' => $lastFailedTimeStamp ? $lastFailedTimeStamp->getTime()->toSql() : null,
            'body' => $data['body'] ?? null,
        ]);

        return $this;
    }

    public function envelope(): Envelope
    {
        $data = [
            'headers' => \json_decode($this->headers, true),
            'body' => $this->body
        ];
        return (new Serializer())->decode($data);
    }

    public function handledBy(): array
    {
        $handlers = [];
        $handlerStamps = $this->envelope()->all(HandledStamp::class);
        /** @var HandledStamp $stamp */
        foreach ($handlerStamps as $stamp) {
            $handlers[] = $stamp->getHandlerName();
        }

        return $handlers;
    }

    public function waiting(): bool
    {
        return $this->sent_on && !$this->received_on;
    }

    public function received(): bool
    {
        return $this->received_on && !$this->handled() && !$this->hasFailed();
    }

    public function hasFailed(): bool
    {
        return !$this->handled_on && $this->last_failed_on;
    }

    public function handled(): bool
    {
        return $this->handled_on ? true : false;
    }
}
