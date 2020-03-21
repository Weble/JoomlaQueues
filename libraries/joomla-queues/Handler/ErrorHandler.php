<?php


namespace Weble\JoomlaQueues\Handler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Weble\JoomlaQueues\Message\ErrorMessage;

class ErrorHandler implements MessageHandlerInterface
{
    public function __invoke(ErrorMessage $message)
    {
        var_dump('here');
        throw new \Exception('Testing Failed Jobs');
    }
}
