<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;
use FOF30\Model\Model;

class Transports extends Model
{
    public function __construct(Container $container, array $config = array())
    {
        parent::__construct($container, $config);
    }

    public function get()
    {
        return $this->container->transport->getTransports();
    }
}
