<?php


namespace Weble\JoomlaQueues\Locator;

use Joomla\CMS\Component\ComponentHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;

class RetryStrategyLocator implements ContainerInterface
{
    public function get($id)
    {
        $config = ComponentHelper::getParams('com_queues');
        return new MultiplierRetryStrategy(
            $config->get('max_retries', 3),
            $config->get('retry_delay', 1000),
            $config->get('retry_multiplier', 1),
            $config->get('max_retry_delay', 0)
        );
    }

    public function has($id)
    {
        return true;
    }

}
