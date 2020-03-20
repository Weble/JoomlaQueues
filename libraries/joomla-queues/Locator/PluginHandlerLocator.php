<?php


namespace Weble\JoomlaQueues\Locator;


use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class PluginHandlerLocator extends HandlersLocator
{
    /**
     * @var array
     */
    private $pluginHandlers;
    /**
     * @var array
     */
    private $registeredHandlers;

    public function __construct(array $handlers = [])
    {
        $this->pluginHandlers = array_merge($this->getMessagesFromPlugins(), $this->getHandlersFromPlugins());
        $this->registeredHandlers = $this->registerHandlers();

        parent::__construct($this->registeredHandlers);
    }

    public function getPluginHandlers(): array
    {
        return $this->pluginHandlers;
    }

    public function getRegisteredHandlers(): array
    {
        return $this->registeredHandlers;
    }

    private function registerHandlers(): array
    {
        $handlersByBusAndMessage = $this->handlersByBusAndMessage();

        foreach ($handlersByBusAndMessage as $bus => $handlersByMessage) {
            foreach ($handlersByMessage as $message => $handlersByPriority) {
                krsort($handlersByPriority);
                $handlersByBusAndMessage[$bus][$message] = array_merge(...$handlersByPriority);
            }
        }

        $handlerDescriptors = [];
        foreach ($handlersByBusAndMessage as $bus => $handlersByMessage) {
            foreach ($handlersByMessage as $message => $handlers) {
                foreach ($handlers as $handler) {
                    $handlerDescriptors[$message][] = new HandlerDescriptor($handler[0], $handler[1]);
                }


            }
        }

        return $handlerDescriptors;
    }

    public function debugHandlers(): array
    {
        $busIds = [
            'default'
        ];

        $debugCommandMapping = $this->handlersByBusAndMessage();
        foreach ($busIds as $bus) {
            if (!isset($debugCommandMapping[$bus])) {
                $debugCommandMapping[$bus] = [];
            }

            foreach ($debugCommandMapping[$bus] as $message => $handlers) {
                foreach ($handlers as $key => $handler) {
                    foreach ($handler as $k => $v) {
                        $debugCommandMapping[$bus][$message][$key][0] = get_class($v[0]);
                        $debugCommandMapping[$bus][$message][$key][1] = $v[1] ?? [];
                    }
                }
            }
        }

        return $debugCommandMapping;
    }

    /**
     * [
     *      Message::class => [
     *          [
     *              'handler' => Handler::class,
     *              'from_transport' => 'default' // optional
     *          ]
     *      ]
     * ]
     * @return array
     */
    private function getHandlersFromPlugins(): array
    {
        PluginHelper::importPlugin('queue');
        $results = Factory::$application->triggerEvent('onGetQueueHandlers', []);

        $handlers = [];
        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $handler => $messages) {
                if (is_array($messages)) {
                    // This is a pure array of classes
                    if (!isset($messages['handles'])) {
                        foreach ($messages as $message) {
                            $handlers[$message][] = [
                                'handler' => $handler
                            ];
                        }
                        continue;
                    }

                    // this is an array of options
                    $handles = $messages['handles'];
                    $options = $messages;
                    $options['handler'] = $handler;
                    unset($options['handles']);

                    foreach ($handles as $message) {
                        $handlers[$message][] = $options;
                    }
                    continue;
                }

                try {
                    $class = new \ReflectionClass($messages);
                } catch (\Exception $e) {
                    continue;
                }

                if ($class->implementsInterface(MessageSubscriberInterface::class)) {
                    $handledMessages = $messages::getHandledMessages();
                    foreach ($handledMessages as $handledMessage => $options) {
                        $options['handler'] = $messages;
                        $handlers[$handledMessage][] = $options;
                    }
                }
            }
        }

        return $handlers;
    }

    /**
     * [
     *      Message::class => [
     *          [
     *              'handler' => Handler::class,
     *              'from_transport' => 'default' // optional
     *          ]
     *      ]
     * ]
     * @return iterable
     */
    private function getMessagesFromPlugins(): array
    {
        PluginHelper::importPlugin('queue');
        $application = Factory::getApplication();
        $results = $application->triggerEvent('onGetQueueMessages', []);

        $handlers = [];
        foreach ($results as $pluginIndex => $pluginMessageList) {
            foreach ($pluginMessageList as $message => $pluginHandlers) {
                foreach ($pluginHandlers as $pluginHandler) {
                    if (is_array($pluginHandler)) {
                        if (!isset($pluginHandler['handler'])) {
                            continue;
                        }

                        $handlers[$message][] = $pluginHandler;
                        continue;
                    }

                    try {
                        $class = new \ReflectionClass($pluginHandler);
                    } catch (\Exception $e) {
                        continue;
                    }

                    if ($class->implementsInterface(MessageSubscriberInterface::class)) {
                        $handledMessages = $pluginHandler::getHandledMessages();
                        foreach ($handledMessages as $handledMessage => $values) {
                            $values['handler'] = $pluginHandler;
                            $handlers[$handledMessage] = $values;
                        }
                    }

                    if ($class->implementsInterface(MessageHandlerInterface::class)) {
                        $handlers[$message][] = [
                            'handler' => $pluginHandler
                        ];
                    }
                }
            }
        }

        return $handlers;
    }

    /**
     * @param $tag
     * @param array $busIds
     * @return array
     * @throws \ReflectionException
     */
    private function handlersByBusAndMessage(): array
    {
        $busIds = [
            'default'
        ];

        $handlersByBusAndMessage = [];
        $registeredHandlers = $this->getPluginHandlers();

        foreach ($registeredHandlers as $messageClass => $handlers) {
            $r = new \ReflectionClass($messageClass);

            if (null === $r) {
                throw new RuntimeException(sprintf('Class "%s" does not exist.', $messageClass));
            }

            foreach ($handlers as $options) {
                $handler = $options['handler'] ?? null;
                $handlerBuses = (array)($options['bus'] ?? $busIds);
                $buses = $handlerBuses;
                unset($options['handler']);

                if (isset($options['bus'])) {
                    $buses = [$options['bus']];
                }

                foreach ($buses as $handlerBus) {
                    $handlersByBusAndMessage[$handlerBus][$messageClass][$options['priority'] ?? 0][] = [
                        new $handler,
                        $options
                    ];
                }
            }

            if (null === $messageClass) {
                throw new RuntimeException(sprintf('Invalid messages "%s::getHandledMessages()" must return one or more messages.', $r->getName()));
            }

            if (null === $handler) {
                throw new RuntimeException(sprintf('Invalid handler class. "%s"  must return one or more messages.', $r->getName()));
            }
        }

        return $handlersByBusAndMessage;
    }
}
