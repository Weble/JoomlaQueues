<?php

namespace Weble\JoomlaQueues\Bus;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\RoutableMessageBus;

class JoomlaMessageBus extends RoutableMessageBus
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $fallbackBus)
    {
        parent::__construct(new ContainerBuilder(), $fallbackBus);

        $this->bus = $fallbackBus;
    }

    public function getMessageBus(string $busName): MessageBusInterface
    {
        return $this->bus;
    }

}
