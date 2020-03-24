<?php
require_once(JPATH_LIBRARIES . '/joomla-queues/bootstrap.php');

FOF30\Container\Container::getInstance('com_queues')->dispatcher->dispatch();

$installer = new \FOF30\Database\Installer(\Joomla\CMS\Factory::getDbo(), JPATH_ADMINISTRATOR . '/components/com_queues/sql/xml/');
$installer->updateSchema();
