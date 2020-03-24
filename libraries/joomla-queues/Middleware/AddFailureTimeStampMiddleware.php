<?php


namespace Weble\JoomlaQueues\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;
use Weble\JoomlaQueues\Stamp\LastFailedTimeStamp;

class AddFailureTimeStampMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // look for "received" messages decorated with the SentToFailureTransportStamp
        /** @var SentToFailureTransportStamp|null $sentToFailureStamp */
        $sentToFailureStamp = $envelope->last(SentToFailureTransportStamp::class);
        if (null !== $sentToFailureStamp) {
            $envelope = $envelope->with(new LastFailedTimeStamp());
        }

        return $stack->next()->handle($envelope, $stack);
    }

}
