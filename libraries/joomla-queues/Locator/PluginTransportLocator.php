<?php


namespace Weble\JoomlaQueues\Locator;


use Joomla\CMS\Factory;
use Joomla\CMS\Http\TransportInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Transport\Sender\SendersLocatorInterface;

/**
 * This class is able to locate the correct transport based on its "name"
 * ie: "database" => DoctrineTransport
 *
 * REMEMBER: The transport is both the sender and the receiver!
 */
class PluginTransportLocator implements SendersLocatorInterface, ContainerInterface
{
    /**
     * Maps a "name" to a transport class for sending messages
     * [
     *      "database" => DoctrineTransport::class
     * ]
     * @var array
     */
    private $transportsMap = [];
    /**
     * Maps a class name to its implementation
     * [
     *      DoctrineTransport::class => DoctrineTransport
     * ]
     * @var array
     */
    private $classMap = [];

    public function __construct()
    {
        PluginHelper::importPlugin('queue-transport');
        $results = Factory::$application->triggerEvent('onGetQueueTransports', []);

        if (!count($results)) {
            return;
        }

        foreach ($results as $pluginIndex => $transports) {
            foreach ($transports as $name => $transportOrConfiguration) {
                $className = get_class($transportOrConfiguration);
                $this->classMap[$className] = $transportOrConfiguration;
                $this->transportsMap[$name] = $className;

                if (!isset($this->transportsMap['*'])) {
                    $this->transportsMap['*'] = $className;
                }
            }
        }
    }

    public function get($id)
    {
        $className = $this->transportsMap[$id] ?? $this->transportsMap['*'] ?? null;
        if (!$className) {
            return null;
        }

        return $this->classMap[$className] ?? null;
    }

    public function has($id)
    {
        return isset($this->transportsMap[$id]);
    }

    /**
     * @return TransportInterface[]
     */
    public function getTransports(): array
    {
        return array_map(function($className) {
            return $this->classMap[$className];
        }, $this->transportsMap);
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
                        throw new RuntimeException(sprintf('Invalid senders configuration: sender "%s" is not in the senders locator.', $senderAlias));
                    }

                    $seen[] = $senderClass;
                    yield $senderClass => $this->classMap[$senderClass];
                }
            }
        }
    }

    public function getReceivers(): array
    {
        $receivers = $this->transportsMap;
        unset($receivers['*']);

        return array_keys($receivers);
    }
}
