<?php


namespace Weble\JoomlaQueues\Transport;


use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
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
     * @var Registry|ProvidesTransport[]
     */
    private $transports;
    /**
     * @var array
     */
    private $senders = [];

    public function __construct(Registry $transports, Registry $messageHandlers)
    {
        $this->transports = $transports;
        $this->loadSenders($messageHandlers);
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
    public function getProviders(): Registry
    {
        return $this->transports;
    }

    /**
     * @param string $id
     * @return TransportInterface
     */
    public function get($id): ?TransportInterface
    {
        return $this->has($id) ? $this->getProvider($id)->transport() : null;
    }

    public function has($id)
    {
        return isset($this->getTransports()[$id]);
    }

    /**
     * @return TransportInterface[]
     */
    public function getTransports(): array
    {
        $transports = [];
        /** @var ProvidesTransport $transportProvider */
        foreach ($this->getProviders() as $transportProvider) {
            $transports[$transportProvider->getKey()] = $transportProvider->transport();
        }

        return $transports;
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

    private function loadSenders(Registry $messageHandlers): void
    {
        foreach ($messageHandlers as $messageClass => $handlerDescriptors) {
            /** @var HandlerDescriptor $handlerDescriptor */
            foreach ($handlerDescriptors as $handlerDescriptor) {
                if (!isset($this->senders[$messageClass])) {
                    $this->senders[$messageClass] = [];
                }

                $this->senders[$messageClass] = array_merge($this->senders[$messageClass], (array)($handlerDescriptor->getOption('transports')) ?: []);
            }
        }
    }
}
