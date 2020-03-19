<?php

namespace Weble\JoomlaQueues\Locator;

use Psr\Container\ContainerInterface;

class ReceiverLocator implements ContainerInterface
{
    private $locators = [];

    public function __construct(array $locators)
    {
        $this->locators = $locators;
    }

    public function get($id)
    {
        return $this->locators[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->locators[$id]);
    }

}
