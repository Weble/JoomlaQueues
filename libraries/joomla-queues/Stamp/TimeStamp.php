<?php


namespace Weble\JoomlaQueues\Stamp;


use Joomla\CMS\Date\Date;
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

    public function getTime(): Date
    {
        return Date::getInstance($this->time->getTimestamp());
    }
}
