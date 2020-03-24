<?php

namespace Weble\JoomlaQueues\Admin\Controller;

use FOF30\Container\Container;
use FOF30\Controller\Controller;

class CPanel extends Controller
{
    public function __construct(Container $container, array $config = array())
    {
        parent::__construct($container, $config);

        $this->setModelName('Jobs');
    }
}
