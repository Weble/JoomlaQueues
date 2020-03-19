<?php


namespace Weble\JoomlaQueues\Handler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Weble\JoomlaQueues\Message\SendEmailMessage;

class SendEmailHandler implements MessageHandlerInterface
{
    public function __invoke(SendEmailMessage $message)
    {
        file_put_contents(JPATH_SITE . '/tmp/test.txt', 'test');
    }
}
