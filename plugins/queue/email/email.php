<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Weble\JoomlaQueues\Handler\SendEmailHandler;
use Weble\JoomlaQueues\Message\SendEmailMessage;

defined('_JEXEC') or die;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgQueueEmail extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        //JLoader::registerNamespace('Weble\\JoomlaCommands\\Commands\\', __DIR__ . '/commands', false, false, 'psr4');
    }

    public function onGetQueueHandlers()
    {
        return [
            SendEmailMessage::class => [
                new SendEmailHandler()
            ]
        ];
    }
}
