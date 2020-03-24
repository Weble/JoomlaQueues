<?php


namespace Weble\JoomlaQueues\Transport;


use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Admin\Container;

/**
 * This class is able to locate the correct transport based on its "name"
 * ie: "database" => DoctrineTransport
 *
 * REMEMBER: The transport is both the sender and the receiver!
 */
class TransportLocator implements SendersLocatorInterface, ContainerInterface
{
    /**
     * @var ProvidesTransport[]
     */
    private $transports = [];
    /**
     * @var array
     */
    private $senders = [];

    public function __construct()
    {
        PluginHelper::importPlugin('queue');
        $this->loadTransports();
        $this->loadSenders();
    }

    /**
     * @param string $id
     * @return ProvidesTransport
     */
    public function getProvider($id): ?ProvidesTransport
    {
        return $this->transports[$id] ?? null;
    }

    /**
     * @return ProvidesTransport[]
     */
    public function getProviders(): array
    {
        return $this->transports;
    }

    /**
     * @param string $id
     * @return TransportInterface
     */
    public function get($id): ?TransportInterface
    {
        return $this->transports[$id] ? $this->transports[$id]->transport() : null;
    }

    public function has($id)
    {
        return isset($this->transports[$id]);
    }

    /**
     * @return TransportInterface[]
     */
    public function getTransports(): array
    {
        return array_map(function (ProvidesTransport $transportProvider) {
            return $transportProvider->transport();
        }, $this->transports);
    }

    /**
     * {@inheritdoc}
     */
    public function getSenders(Envelope $envelope): iterable
    {
        $seen = [];

        foreach (HandlersLocator::listTypes($envelope) as $type) {
            foreach ($this->senders[$type] ?? [] as $transport) {
                if (\in_array($transport, $seen, true)) {
                    continue;
                }

                if (!$this->has($transport)) {
                    throw new RuntimeException(sprintf('Invalid senders configuration: sender "%s" is not registered.', $transport));
                }

                $seen[] = $transport;
                $transport = $this->get($transport);
                yield get_class($transport) => $transport;
            }
        }

        if (count($seen) > 0) {
            return;
        }

        $defaultTransportKey = ComponentHelper::getParams('com_queues')->get('default_transport', 'database');

        if (\in_array($defaultTransportKey, $seen, true)) {
            return;
        }

        $defaultTransport = $this->get($defaultTransportKey);
        yield get_class($defaultTransport) => $defaultTransport;
    }

    /**
     * @return ReceiverInterface[]
     */
    public function getReceivers(): array
    {
        return array_keys($this->getTransports());
    }

    private function loadTransports(): void
    {
        $results = Factory::$application->triggerEvent('onGetQueueTransports', []);

        /**
         * @var int $pluginIndex
         * @var ProvidesTransport[] $transportProviders
         */
        foreach ($results as $pluginIndex => $transportProviders) {
            foreach ($transportProviders as $transportProvider) {
                $this->transports[$transportProvider->getKey()] = $transportProvider;
            }
        }
    }

    private function loadSenders(): void
    {
        $results = Factory::$application->triggerEvent('onGetQueueMessages', []);
        foreach ($results as $pluginIndex => $messages) {
            foreach ($messages as $messageClass => $senderConfigurations) {
                foreach ($senderConfigurations as $senderConfiguration) {
                    if (!is_array($senderConfiguration)) {
                        continue;
                    }

                    if (!isset($this->senders[$messageClass])) {
                        $this->senders[$messageClass] = [];
                    }

                    $this->senders[$messageClass] = array_merge($this->senders[$messageClass], $senderConfiguration['transports'] ?? []);
                }
            }
        }
    }
}
