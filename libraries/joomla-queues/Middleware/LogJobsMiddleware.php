<?php


namespace Weble\JoomlaQueues\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Admin\Model\Jobs;
use Weble\JoomlaQueues\Stamp\JobIdStamp;

class LogJobsMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var Container $container */
        $container = Container::getInstance('com_queues', [], 'admin');

        /** @var Jobs $model */
        $model = $container->factory->model('Jobs')->tmpInstance();
        $model = $model->fromEnvelope($envelope)->save();

        if (null === $envelope->last(JobIdStamp::class)) {
            $envelope = $envelope->with(new JobIdStamp($model->getId()));
        }

        return $stack->next()->handle($envelope, $stack);
    }

}
