<?php
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

FOF30\Container\Container::getInstance('com_queues')->dispatcher->dispatch();

$installer = new \FOF30\Database\Installer(\Joomla\CMS\Factory::getDbo(), JPATH_ADMINISTRATOR . '/components/com_queues/sql/xml/');
$installer->updateSchema();
