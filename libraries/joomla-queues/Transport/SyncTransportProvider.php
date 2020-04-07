<?php


namespace Weble\JoomlaQueues\Transport;


use FOF30\Container\Container;
use Joomla\Registry\Registry;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class SyncTransportProvider extends TransportProvider
{
    protected $name = 'sync';
    protected $params;

    public function __construct(Registry $params = null)
    {
        $this->params = $params;
    }

    public function getKey(): string
    {
        return $this->name;
    }

    public function transport(): TransportInterface
    {
        /** @var \Weble\JoomlaQueues\Admin\Container $container */
        $container = Container::getInstance('com_queues', [], 'admin');
        return new SyncTransport(
            $container->bus->defaultBus()
        );
    }

    public function retryStrategy(): RetryStrategyInterface
    {
        if (!$this->params) {
            return parent::retryStrategy();
        }

        if (!$this->params->get('override_retry_strategy', 0)) {
            return parent::retryStrategy();
        }

        return $this->useMultiplierRetryStrategy($this->params);
    }
}
