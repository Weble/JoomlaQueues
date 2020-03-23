<?php
// no direct access
use Enqueue\Dbal\DbalConnectionFactory;
use FOF30\Database\Installer;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

// Load FOF
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    return;
}

require_once JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php';

class Com_QueuesInstallerScript
{
    public function postflight($type, $parent)
    {
        $installer = new Installer(Factory::getDbo(), JPATH_ADMINISTRATOR . '/components/com_queues/sql/xml/');
        $installer->updateSchema();
    }
}
