<?php

namespace Weble\JoomlaQueues\Transport;

use FOF30\Model\Exception\CannotGetName;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

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
