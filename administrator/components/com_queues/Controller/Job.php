<?php

namespace Weble\JoomlaQueues\Admin\Controller;

use FOF30\Container\Container;
use FOF30\Controller\DataController;
use Symfony\Component\Messenger\Envelope;
use Weble\JoomlaQueues\Message\SendEmailMessage;

class Job extends DataController
{
    public function __construct(Container $container, array $config = array())
    {
        parent::__construct($container, $config);
    }

}
