<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Joomla\CMS\Http\TransportInterface;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Locator\PluginTransportLocator;

class Transport
{
    /** @var  Container  The container we belong to */
    protected $container = null;

    /**
     * @var PluginTransportLocator
     */
    private $transportLocator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->transportLocator = new PluginTransportLocator();
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
}
