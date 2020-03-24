<?php

namespace Weble\JoomlaQueues\Admin\Service;

use Joomla\Registry\Registry;
use RuntimeException;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Weble\JoomlaQueues\Admin\Container;
use Weble\JoomlaQueues\Bus\BusProvider;
use Weble\JoomlaQueues\Transport\ProvidesTransport;

class Configuration
{
    private const PLUGIN_GROUP = 'queue';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Registry
     */
    private $transportProviders;
    /**
     * @var Registry
     */
    private $messages;

    /**
     * @var Registry
     */
    private $buses;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $allowPluginsInCLI = $this->container->platform->isAllowPluginsInCli();
        $this->container->platform->setAllowPluginsInCli(true);
        $this->container->platform->importPlugin(self::PLUGIN_GROUP);

        $this->loadBusesConfiguration();
        $this->loadTransportsConfiguration();
        $this->loadMessagesAndHandlersConfiguration();

        $this->container->platform->setAllowPluginsInCli($allowPluginsInCLI);
    }

    public function messageHandlers(): Registry
    {
        return $this->messages;
    }

    public function buses(): Registry
    {
        return $this->buses;
    }

    public function transportProviders(): Registry
    {
        return $this->transportProviders;
    }

    public function busIds(): array
    {
        return array_keys($this->buses()->toArray());
    }

    public function debugHandlers(): array
    {
        $busIds = $this->busIds();

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

    public function handlersByBusAndMessage(): array
    {
        $busIds = $this->busIds();

        $handlersByBusAndMessage = [];

        foreach ($this->buses() as $messageClass => $handlerDescriptors) {
            /** @var HandlerDescriptor $handlerDescriptor */
            foreach ($handlerDescriptors as $handlerDescriptor) {
                $handlerBuses = (array)($handlerDescriptor->getOption('bus') ?: $busIds);
                $buses = $handlerBuses;

                foreach ($buses as $handlerBus) {
                    $handlersByBusAndMessage[$handlerBus][$messageClass][$handlerDescriptor->getOption('priority') ?: 0][] = $handlerDescriptor;
                }
            }
        }

        return $handlersByBusAndMessage;
    }

    private function loadBusesConfiguration()
    {
        if ($this->buses) {
            return;
        }

        $this->buses = new Registry();

        $results = $this->container->platform->runPlugins('onGetQueueBuses', []);

        /**
         * @var $busProviders BusProvider[]
         */
        foreach ($results as $pluginIndex => $busProviders) {
            /**
             * @var $bus BusProvider
             */
            foreach ($busProviders as $busName => $bus) {
                $this->buses->set($bus->getKey(), $bus);
            }
        }
    }

    private function loadTransportsConfiguration()
    {
        if ($this->transportProviders) {
            return;
        }

        $this->transportProviders = new Registry();
        $results = $this->container->platform->runPlugins('onGetQueueTransports', []);

        /**
         * @var int $pluginIndex
         * @var ProvidesTransport[] $transportProviders
         */
        foreach ($results as $pluginIndex => $transportProviders) {
            foreach ($transportProviders as $transportProvider) {
                $this->transportProviders->set($transportProvider->getKey(), $transportProvider);
            }
        }
    }

    private function loadMessagesAndHandlersConfiguration()
    {
        if ($this->messages) {
            return;
        }

        $this->messages = new Registry();

        $handlers = array_merge($this->loadMessagesConfiguration(), $this->loadHandlersConfiguration());
        $this->messages->loadArray($handlers);
    }

    private function loadMessagesConfiguration(): array
    {
        $results = $this->container->platform->runPlugins('onGetQueueMessages', []);
        $handlers = [];
        foreach ($results as $pluginIndex => $messages) {
            foreach ($messages as $messageClass => $senderConfigurations) {
                // This can be a class (ie: SendEmailHandler::class)
                // Or can be an array of options (ie: ['handler' => PingHandler::class, 'transports' => ['database']])
                // Let's make them all the same using HandlerDescription
                foreach ($senderConfigurations as $senderConfiguration) {
                    $handlerDescriptor = $this->getHandlerDescriptionFromConfiguration($senderConfiguration);
                    $handlers[$messageClass][] = $handlerDescriptor;
                }
            }
        }

        return $handlers;
    }

    private function loadHandlersConfiguration(): array
    {
        $results = $this->container->platform->runPlugins('onGetQueueHandlers', []);
        $busIds = array_keys($this->buses()->toArray());

        $handlers = [];
        foreach ($results as $pluginIndex => $pluginHandlerList) {
            foreach ($pluginHandlerList as $handlerClass => $configuration) {

                // just a class name of the handler
                if (is_string($configuration)) {
                    $handlerClass = $configuration;
                    $configuration = [];
                }

                if (isset($configuration['bus']) && !\in_array($configuration['bus'], $busIds, true)) {
                    throw new RuntimeException(sprintf('Invalid handler service: bus "%s" specified on the handler "%s" does not exist (known ones are: %s).', $configuration['bus'], $handlerClass, implode(', ', $busIds)));
                }

                $r = new \ReflectionClass($handlerClass);

                if (null === $r) {
                    throw new RuntimeException(sprintf('Invalid service: class "%s" does not exist.', $handlerClass));
                }

                if (isset($configuration['handles'])) {
                    $handles = isset($configuration['method']) ? [$configuration['handles'] => $configuration['method']] : (array) $configuration['handles'];
                } else {
                    $handles = $this->guessHandledClasses($r);
                }

                $message = null;
                foreach ($handles as $message => $options) {
                    if (\is_int($message)) {
                        if (\is_string($options)) {
                            $message = $options;
                            $options = [];
                        } else {
                            var_dump($message, $options);die();
                            throw new  RuntimeException(sprintf('The handler configuration needs to return an array of messages or an associated array of message and configuration. Found value of type "%s" at position "%d" for handler "%s".', \gettype($options), $message, $handlerClass));
                        }
                    }

                    if (\is_string($options)) {
                        $options = ['method' => $options];
                    }

                    if (!isset($options['from_transport']) && isset($configuration['from_transport'])) {
                        $options['from_transport'] = $configuration['from_transport'];
                    }

                    $options['priority'] = $options['priority'] ?? $configuration['priority'] ?? 0;
                    $method = $options['method'] ?? '__invoke';

                    if (isset($options['bus'])) {
                        if (!\in_array($options['bus'], $busIds)) {
                            $messageLocation = isset($configuration['handles']) ? 'declared in your tag attribute "handles"' : ($r->implementsInterface(MessageSubscriberInterface::class) ? sprintf('returned by method "%s::getHandledMessages()"', $r->getName()) : sprintf('used as argument type in method "%s::%s()"', $r->getName(), $method));

                            throw new RuntimeException(sprintf('Invalid configuration %s for message "%s": bus "%s" does not exist.', $messageLocation, $message, $options['bus']));
                        }
                    }

                    if ('*' !== $message && !class_exists($message) && !interface_exists($message, false)) {
                        $messageLocation = isset($configuration['handles']) ? 'declared in your tag attribute "handles"' : ($r->implementsInterface(MessageSubscriberInterface::class) ? sprintf('returned by method "%s::getHandledMessages()"', $r->getName()) : sprintf('used as argument type in method "%s::%s()"', $r->getName(), $method));

                        throw new RuntimeException(sprintf('Invalid handler service %s: class or interface "%s" %s not found.', $handlerClass, $message, $messageLocation));
                    }

                    if (!$r->hasMethod($method)) {
                        throw new RuntimeException(sprintf('Invalid handler service "%s": method "%s::%s()" does not exist.', $handlerClass, $r->getName(), $method));
                    }


                    if ('__invoke' !== $method) {
                        $handler = \Closure::fromCallable([
                            $handlerClass,
                            $method
                        ]);
                    } else {
                        $handler = new $handlerClass();
                    }

                    $handlers[$message][] = new HandlerDescriptor($handler, $options);
                }

                if (null === $message) {
                    throw new RuntimeException(sprintf('Invalid handler service "%s": method "%s::getHandledMessages()" must return one or more messages.', $handlerClass, $r->getName()));
                }
            }
        }

        return $handlers;
    }

    private function getHandlerDescriptionFromConfiguration($senderConfiguration): HandlerDescriptor
    {
        // just a class name of the handler
        if (is_string($senderConfiguration)) {
            $handler = new $senderConfiguration();
            return new HandlerDescriptor($handler, []);
        }

        if (\is_callable($senderConfiguration)) {
            $handler = \Closure::fromCallable($senderConfiguration);
            return new HandlerDescriptor($handler, []);
        }

        if (!is_array($senderConfiguration) || !isset($senderConfiguration['handler'])) {
            throw new RuntimeException(sprintf('Invalid handler configuration: %s', json_encode($senderConfiguration)));
        }

        $handlerDescriptor = $this->getHandlerDescriptionFromConfiguration($senderConfiguration['handler']);
        unset($senderConfiguration['handler']);
        return new HandlerDescriptor($handlerDescriptor->getHandler(), $senderConfiguration);
    }

    private function guessHandledClasses(\ReflectionClass $handlerClass): iterable
    {
        if ($handlerClass->implementsInterface(MessageSubscriberInterface::class)) {
            return $handlerClass->getName()::getHandledMessages();
        }

        try {
            $method = $handlerClass->getMethod('__invoke');
        } catch (\ReflectionException $e) {
            throw new RuntimeException(sprintf('Invalid handler service: class "%s" must have an "__invoke()" method.', $handlerClass->getName()));
        }

        if (0 === $method->getNumberOfRequiredParameters()) {
            throw new RuntimeException(sprintf('Invalid handler service: method "%s::__invoke()" requires at least one argument, first one being the message it handles.', $handlerClass->getName()));
        }

        $parameters = $method->getParameters();
        if (!$type = $parameters[0]->getType()) {
            throw new RuntimeException(sprintf('Invalid handler service: argument "$%s" of method "%s::__invoke()" must have a type-hint corresponding to the message class it handles.', $parameters[0]->getName(), $handlerClass->getName()));
        }

        if ($type->isBuiltin()) {
            throw new RuntimeException(sprintf('Invalid handler service: type-hint of argument "$%s" in method "%s::__invoke()" must be a class , "%s" given.', $parameters[0]->getName(), $handlerClass->getName(), $type instanceof \ReflectionNamedType ? $type->getName() : (string)$type));
        }

        return [$parameters[0]->getType()->getName()];
    }
}
