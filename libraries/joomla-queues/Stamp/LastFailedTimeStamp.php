<?php


namespace Weble\JoomlaQueues\Stamp;


class LastFailedTimeStamp extends TimeStamp
{
    private $originalMessageId;

    public function __construct($originalMessageId = null)
    {
        parent::__construct();

        $this->originalMessageId = $originalMessageId;
    }

    public function getOriginalMessageId()
    {
        return $this->originalMessageId;
    }
}
