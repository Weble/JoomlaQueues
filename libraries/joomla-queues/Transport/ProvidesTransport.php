<?php


namespace Weble\JoomlaQueues\Transport;


use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface ProvidesTransport
{
    public function getName(): string;

    public function getKey(): string;

    public function transport(): TransportInterface;

    public function serializer(): SerializerInterface;

    public function retryStrategy(): RetryStrategyInterface;
}
