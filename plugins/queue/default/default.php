<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Weble\JoomlaQueues\Bus\DefaultBusProvider;
use Weble\JoomlaQueues\Transport\DatabaseTransportProvider;

defined('_JEXEC') or die;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgQueueDefault extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;
    protected static $transports = [];
    protected $tableName = 'queues_jobs';

    public function onGetQueueBuses()
    {
        return [
            new DefaultBusProvider()
        ];
    }

    public function onGetQueueTransports()
    {
        return [
            new DatabaseTransportProvider('default'),
            new DatabaseTransportProvider('failure')
        ];
    }
}
