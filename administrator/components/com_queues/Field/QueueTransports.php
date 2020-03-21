<?php

use FOF30\Container\Container;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}

class JFormFieldQueueTransports extends JFormFieldList
{
    protected $type = 'QueueTransports';

    protected function getOptions()
    {
        return array_map(function ($transportKey) {
            $obj = new stdClass();
            $obj->value = $transportKey;
            $obj->text = $transportKey;
            return $obj;
        }, array_keys(Container::getInstance('com_queues')->factory->model('Transports')->tmpInstance()->get()));
    }

}
