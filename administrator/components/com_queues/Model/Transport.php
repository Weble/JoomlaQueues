<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;
use FOF30\Model\Model;

class Transport extends Model
{
    public function get()
    {
        return $this->container->transport->getTransports();
    }
}
