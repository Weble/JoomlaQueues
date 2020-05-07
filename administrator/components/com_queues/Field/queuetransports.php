<?php

use FOF30\Container\Container;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}

class JFormFieldQueueTransports extends JFormFieldList
{
    protected $type = 'queuetransports';

    protected function getOptions()
    {
        /** @var \Weble\JoomlaQueues\Admin\Container $container */
        $container = Container::getInstance('com_queues');
        return array_map(function ($transportKey) {
            $obj = new stdClass();
            $obj->value = $transportKey;
            $obj->text = $transportKey;
            return $obj;
        }, $container->transport->getTransportKeys());
    }

}
