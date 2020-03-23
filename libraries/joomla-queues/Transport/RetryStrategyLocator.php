<?php


namespace Weble\JoomlaQueues\Transport;

use Joomla\CMS\Component\ComponentHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;

/**
 * Locates the retry strategy based on the transport Key
 */
class RetryStrategyLocator implements ContainerInterface
{
    /**
     * @var TransportLocator
     */
    private $transportLocator;

    public function __construct(TransportLocator $transportLocator)
    {
        $this->transportLocator = $transportLocator;
    }

    public function get($id)
    {
        if ($this->transportLocator->has($id)) {
            return $this->transportLocator->getProvider($id)->retryStrategy();
        }

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
        return $this->transportLocator->has($id);
    }

}
