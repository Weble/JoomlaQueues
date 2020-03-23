<?php


namespace Weble\JoomlaQueues\Stamp;


use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

abstract class TimeStamp implements StampInterface
{
    /**
     * @var \DateTimeImmutable
     */
    protected $time;

    public function __construct()
    {
        $this->time = new \DateTimeImmutable();
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }
}
