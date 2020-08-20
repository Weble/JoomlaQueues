<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Weble\JoomlaQueues\Bus\DefaultBusProvider;
use Weble\JoomlaQueues\Transport\DatabaseTransportProvider;
use Weble\JoomlaQueues\Transport\RedisTransportProvider;
use Weble\JoomlaQueues\Transport\SyncTransportProvider;

defined('_JEXEC') or die;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgQueueDefault extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;

    public function onGetQueueBuses()
    {
        return [
            new DefaultBusProvider()
        ];
    }

    public function onGetQueueTransports()
    {
        return [
            new SyncTransportProvider($this->params),
            new DatabaseTransportProvider('default', $this->params),
            new DatabaseTransportProvider('failure', $this->params),
            new RedisTransportProvider($this->params),
        ];
    }
}
