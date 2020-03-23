<?php

use FOF30\Container\Container;
use Weble\JoomlaQueues\Bus\ProvidesBus;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}

class JFormFieldQueueBuses extends JFormFieldList
{
    protected $type = 'QueueBuses';

    protected function getOptions()
    {
        /** @var \Weble\JoomlaQueues\Admin\Container $container */
        $container = Container::getInstance('com_queues');
        return array_map(function (ProvidesBus $busProvider) {
            $obj = new stdClass();
            $obj->value = $busProvider->getKey();
            $obj->text = $busProvider->getName();
            return $obj;
        }, $container->bus->getBuses());
    }

}
