<?php

namespace Weble\JoomlaQueues\Transport;

use FOF30\Model\Exception\CannotGetName;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\RejectRedeliveredMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Weble\JoomlaQueues\Handler\HandlersLocator;

abstract class TransportProvider implements ProvidesTransport
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $key;

    public function getName(): string
    {
        if (!$this->name) {
            $r = null;

            if (!preg_match('/(.*)\\\\TransportProvider\\\\(.*)/i', get_class($this), $r)) {
                throw new CannotGetName;
            }

            $this->name = $r[2];
        }

        return $this->name;
    }

    public function getKey(): string
    {
        if (!$this->key) {
            $this->key = ApplicationHelper::stringURLSafe(strtolower($this->getName()));
        }

        return $this->key;
    }

    abstract public function transport(): TransportInterface;

    public function retryStrategy(): RetryStrategyInterface
    {
        return $this->useMultiplierRetryStrategy();
    }

    /**
     * @return Serializer
     */
    public function serializer(): SerializerInterface
    {
        return new Serializer();
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function middlewares(): array
    {
        $before = $this->beforeMiddlewares();
        $custom = $this->customMiddlewares();
        $after = $this->afterMiddlewares();

        return $before + $custom + $after;
    }

    /**
     * @return MiddlewareInterface[]
     */
    protected function customMiddlewares(): array
    {
        return [];
    }

    protected function useMultiplierRetryStrategy(?Registry $config = null): MultiplierRetryStrategy
    {
        if ($config === null) {
            $config = ComponentHelper::getParams('com_queues');
        }

        return new MultiplierRetryStrategy(
            $config->get('max_retries', 3),
            $config->get('retry_delay', 1000),
            $config->get('retry_multiplier', 1),
            $config->get('max_retry_delay', 0)
        );
    }

    /**
     * @return MiddlewareInterface[]
     */
    protected function beforeMiddlewares(): array
    {
        return [
            new AddBusNameStampMiddleware('default'),
            new RejectRedeliveredMessageMiddleware(),
            new DispatchAfterCurrentBusMiddleware(),
            new FailedMessageProcessingMiddleware(),
        ];
    }

    /**
     * @return MiddlewareInterface[]
     */
    protected function afterMiddlewares(): array
    {
        $transportLocator = new TransportLocator();
        $dispatcher = new EventDispatcher();

        return [
            new SendMessageMiddleware(
                $transportLocator,
                $dispatcher
            ),
            new HandleMessageMiddleware(
                new HandlersLocator()
            ),
        ];
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }
}
