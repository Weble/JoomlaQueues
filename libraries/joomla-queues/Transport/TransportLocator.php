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
use Symfony\Component\Messenger\Transport\TransportInterface;

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
    private $transports;

    public function __construct()
    {
        PluginHelper::importPlugin('queue');
        $results = Factory::$application->triggerEvent('onGetQueueTransports', []);

        if (!count($results)) {
            return;
        }

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

    /**
     * @param string $id
     * @return ProvidesTransport
     */
    public function getProvider($id)
    {
        return $this->transports[$id] ?? null;
    }

    /**
     * @param string $id
     * @return TransportInterface
     */
    public function get($id)
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
        return array_map(function(ProvidesTransport $transportProvider){
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
            foreach ($this->classMap[$type] ?? [] as $senderClass) {
                if (!\in_array($senderClass, $seen, true)) {
                    if (!class_exists($senderClass)) {
                        throw new RuntimeException(sprintf('Invalid senders configuration: sender "%s" is not in the senders locator.', $senderClass));
                    }

                    $seen[] = $senderClass;
                    yield $senderClass => $this->classMap[$senderClass];
                }
            }
        }

        if (count($seen) > 0) {
            return;
        }

        $defaultTransportKey = ComponentHelper::getParams('com_queues')->get('default_transport', 'database');
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
}
