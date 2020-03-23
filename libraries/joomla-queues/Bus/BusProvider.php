<?php

namespace Weble\JoomlaQueues\Bus;

use FOF30\Model\Exception\CannotGetName;
use Joomla\CMS\Application\ApplicationHelper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use Symfony\Component\Messenger\Middleware\DispatchAfterCurrentBusMiddleware;
use Symfony\Component\Messenger\Middleware\FailedMessageProcessingMiddleware;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\RejectRedeliveredMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Weble\JoomlaQueues\Handler\HandlersLocator;
use Weble\JoomlaQueues\Transport\TransportLocator;

abstract class BusProvider implements ProvidesBus
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $key;

    public function getName(): string
    {
        if (!$this->name) {
            $r = null;

            if (!preg_match('/(.*)\\\\BusProvider\\\\(.*)/i', get_class($this), $r)) {
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

    abstract public function bus(): MessageBusInterface;

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

    /**
     * @return MiddlewareInterface[]
     */
    protected function beforeMiddlewares(): array
    {
        return [
            new AddBusNameStampMiddleware($this->getKey()),
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
