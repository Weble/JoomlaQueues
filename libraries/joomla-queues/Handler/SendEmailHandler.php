<?php


namespace Weble\JoomlaQueues\Handler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Weble\JoomlaQueues\Message\SendEmailMessage;

class SendEmailHandler implements MessageHandlerInterface
{
    public function __invoke(SendEmailMessage $message)
    {
        var_dump($message);
    }
}
