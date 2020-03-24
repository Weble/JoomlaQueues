<?php

use Joomla\CMS\Plugin\CMSPlugin;
use Weble\JoomlaQueues\Handler\ErrorHandler;
use Weble\JoomlaQueues\Handler\PingHandler;
use Weble\JoomlaQueues\Handler\SendEmailHandler;
use Weble\JoomlaQueues\Message\ErrorMessage;
use Weble\JoomlaQueues\Message\PingMessage;
use Weble\JoomlaQueues\Message\SendEmailMessage;

defined('_JEXEC') or die;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

class PlgQueueQueueExample extends CMSPlugin
{
    protected $app;
    protected $autoloadLanguage = true;

    /**
     * Get the available messages, and optionally route them to their handlers / transports
     */
    public function onGetQueueMessages()
    {
        $transports = $this->params->get('ping_message_transports', null);

        return [
            // This goes to the default transport configured in the admin parameters
            SendEmailMessage::class => [
                SendEmailHandler::class
            ],
            // This should fail and get logged to the failed queue
            ErrorMessage::class     => [
                ErrorHandler::class
            ],
            // This goes to the specified transports
            // you can get the transports through the container:
            // $container->transport->getTransportsKeys(); ['database']
            PingMessage::class      => [
                [
                    'handler'    => PingHandler::class,
                    'transports' => $transports
                    // 'method' => 'otherMethod'
                ]
            ]
        ];
    }

    /**
     * Get the avaiable Queue Handlers, optionally specifying other options
     * Implementing this event is not require, if you already routed the messages
     * to their handler using the onGetQueueMessages.
     *
     * Also, the Handler can configure itself if it implements the MessageSubscriberInterface
     *
     * @see onGetQueueMessages method
     */
    public function onGetQueueHandlers()
    {
        return [
            SendEmailHandler::class => [
                "handles" => [
                    SendEmailMessage::class
                ],
                // "bus" => Container::getInstance('com_queues')->bus->getName(),
                // "from_transport" => 'default' ,
                // "method" => "someOtherHandlerClassMethodInsteadOfInvoke",
                // "priority" => 0
            ],
            // this one self implements through the MessageSubscriberInterface
            PingHandler::class
        ];
    }
}
