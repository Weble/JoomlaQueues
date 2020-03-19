<?php


namespace Weble\JoomlaQueues\Admin\Model;


use FOF30\Container\Container;
use FOF30\Model\DataModel;

class Jobs extends DataModel
{
    public function __construct(Container $container, array $config = array())
    {
        $config['idFieldName'] = 'id';

        parent::__construct($container, $config);
    }
}
