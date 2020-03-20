<?php


namespace Weble\JoomlaQueues\Handler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Weble\JoomlaQueues\Message\SendEmailMessage;

class SendEmailHandler implements MessageHandlerInterface
{
    public function __invoke(SendEmailMessage $message)
    {
        $mailer = \JFactory::getMailer();
        $mailer->addRecipient('daniele@weble.it');
        $mailer->setSubject('test');
        $mailer->setBody('test');
        $mailer->Send();
    }
}
