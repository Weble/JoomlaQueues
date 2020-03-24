<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Transport\ProvidesTransport;
use Weble\JoomlaQueues\Transport\RetryStrategyLocator;
use Weble\JoomlaQueues\Transport\TransportLocator;

class Transport
{
    /** @var  Container  The container we belong to */
    protected $container;

    /**
     * @var TransportLocator
     */
    private $transportLocator;
    /**
     * @var RetryStrategyLocator
     */
    private $retryStrategyLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->transportLocator = new TransportLocator(
            $this->container->queueConfig->transportProviders(),
            $this->container->queueConfig->messageHandlers()
        );
        $this->retryStrategyLocator = new RetryStrategyLocator($this->transportLocator);
    }

    /**
     * @return TransportInterface[]
     */
    public function getTransports(): array
    {
        return $this->transportLocator->getTransports();
    }

    public function getTransport(string $id): ?TransportInterface
    {
        return $this->getTransports()[$id] ?? null;
    }

    /**
     * @return string[]
     */
    public function getTransportKeys(): array
    {
        $transports = $this->getTransports();
        return array_keys($transports);
    }

    public function locator(): TransportLocator
    {
        return $this->transportLocator;
    }

    /**
     * @return ProvidesTransport[]
     */
    public function providers(): array
    {
        return $this->transportLocator->getProviders();
    }

    public function retryStrategyLocator(): RetryStrategyLocator
    {
        return $this->retryStrategyLocator;
    }

    public function failureTransportName(): string
    {
        return $this->container->params->get('failure_transport', 'failure');
    }

    public function failureTransport(): TransportInterface
    {
        return $this->getTransport($this->failureTransportName());
    }
}
