<?php


namespace Weble\JoomlaQueues\Middleware;


use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

/**
 * Checks whether the connection is still open or reconnects otherwise.
 *
 * @author Fuong <insidestyles@gmail.com>
 */
class DoctrineCloseConnectionMiddleware implements MiddlewareInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            if (null !== $envelope->last(ConsumedByWorkerStamp::class)) {
                $this->connection->close();
            }
        }
    }
}
