# Joomla Queues

Implementation of symfony/messenger for Joomla! to run background commands using queues.
See [https://symfony.com/doc/current/messenger.html](https://symfony.com/doc/current/messenger.html)

## Requirements

First, you need to have our Joomla Commands package installed:
[https://github.com/Weble/JoomlaCommands](https://github.com/Weble/JoomlaCommands)

After that you can write messages and handlers to dispatched and run through a queue.
By default we provide a default queue using the database transport, with a default table that stores the jobs to be processed.

Alternatively you can create new transport and new buses to use however you see fit.

## Available Commands

- ```php bin/console  messenger:consume ``` - Consume messages in a queue. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker) for instructions

## Creating messags and handlers

To add new messages with their handlers, create a Joomla! plugin in the **queue** group, and enable it.
Then add a ```onGetQueueHandlers``` method, and return an associative array of messages with a list of their handlers.

Check the Email Handler plugin as an example:

```php
    public function onGetQueueHandlers()
    {
        return [
            \Weble\JoomlaQueues\Message\SendEmailMessage::class => [
                new \Weble\JoomlaQueues\Handler\SendEmailHandler()
            ]
        ];
    }
```
