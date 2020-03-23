<?php


namespace Weble\JoomlaQueues\Bus;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;

interface ProvidesBus
{
    public function getName(): string;

    public function getKey(): string;

    public function bus(): MessageBusInterface;

    /** @return MiddlewareInterface[] */
    public function middlewares(): array;
}
