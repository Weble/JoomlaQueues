<?php


namespace Weble\JoomlaQueues\Locator;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;

class RetryStrategyLocator implements ContainerInterface
{
    public function get($id)
    {
        return new MultiplierRetryStrategy();
    }

    public function has($id)
    {
        return true;
    }

}
