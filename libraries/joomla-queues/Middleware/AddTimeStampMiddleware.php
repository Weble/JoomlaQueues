<?php


namespace Weble\JoomlaQueues\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class AddTimeStampMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $timeStampClass;

    public function __construct(string $timeStampClass)
    {
        $this->timeStampClass = $timeStampClass;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last($this->timeStampClass)) {
            $stamp = new $this->timeStampClass;
            var_dump($stamp);
            $envelope = $envelope->with($stamp);
        }

        return $stack->next()->handle($envelope, $stack);
    }

}
