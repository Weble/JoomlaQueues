<?php


namespace Weble\JoomlaQueues\Stamp;


use Symfony\Component\Messenger\Stamp\StampInterface;
use Weble\JoomlaQueues\Admin\Model\Jobs;

class JobIdStamp implements StampInterface
{
    private $jobId;

    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }

    public function getJobId()
    {
        return $this->jobId;
    }
}
