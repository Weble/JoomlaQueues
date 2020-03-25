# Joomla Queues

Implementation of symfony/messenger for Joomla! to run background commands using queues.
See [https://symfony.com/doc/current/messenger.html](https://symfony.com/doc/current/messenger.html)

## Requirements

First, you need to have our Joomla Commands package installed:
[https://github.com/Weble/JoomlaCommands](https://github.com/Weble/JoomlaCommands)
After that you can write messages and handlers to dispatched and run through a queue.

By default we provide a default bus and a default transport using the database driver, with a default table that stores the jobs to be processed.

Additionally, we store failed jobs using the same transport, but a different queue name, and we log each message passing through the default bus in a separate table, allowing you to **monitor the job processing status.**

[IMG HERE]

Alternatively you can create new transport and new buses to use however you see fit.

## Available Commands

- ```php bin/console messenger:consume ``` - Consume messages in a queue. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker) for instructions
- ```php bin/console messenger:failed:remove``` - Remove a message from the failure transport. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#saving-retrying-failed-messages) for instructions
- ```php bin/console messenger:failed:retry``` - Retries one or more messages from the failure transport. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#saving-retrying-failed-messages) for instructions
- ```php bin/console messenger:failed:show``` - Shows one or more messages from the failure transport. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#saving-retrying-failed-messages) for instructions
- ```php bin/console messenger:stop-workers``` - Stops workers after their current message. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#deploying-to-production) for instructions
- ```debug:messenger``` - - ```debug:messenger``` - Remove a message from the failure transport. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker) for instructions. Refer to [symfony's documentation](https://symfony.com/doc/current/messenger.html#creating-a-message-handler) for instructions

## Creating messages and handlers

To add new messages with their handlers, create a Joomla! **plugin** in the **queue** group, and enable it.
You then have two ways to register messages and handlers.

This configuration resembles very closely the symfony's one:
- [https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport](https://symfony.com/doc/current/messenger.html#binding-handlers-to-different-transports)
- [https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport](https://symfony.com/doc/current/messenger.html#routing-messages-to-a-transport)
- [https://symfony.com/doc/current/messenger.html#handler-subscriber-options](https://symfony.com/doc/current/messenger.html#handler-subscriber-options)
- [https://symfony.com/doc/current/messenger.html#binding-handlers-to-different-transports](https://symfony.com/doc/current/messenger.html#binding-handlers-to-different-transports)

#### 1. ```onGetQueueMessages``` 

Add a ```onGetQueueMessages```  method, and return an associative array of messages with a list of their handlers.
Check the Email Handler plugin as an example:

```php
    public function onGetQueueMessages()
    {
        $transports = $this->params->get('ping_message_transports', null);

        return [
            // This goes to the default transport configured in the admin parameters
             \Weble\JoomlaQueues\Message\SendEmailMessage::class => [
                // could also be new \Weble\JoomlaQueues\Handler\SendEmailHandler()
                \Weble\JoomlaQueues\Handler\SendEmailHandler::class
            ],
            // This should fail and get logged to the failed queue
            \Weble\JoomlaQueues\Message\ErrorMessage::class  => [
                \Weble\JoomlaQueues\Handler\ErrorHandler::class
            ],
            // This goes to the specified transports
            // you can get the transports through the container:
            // calling \FOF30\Container\Container::getInstance('com_queues')->transport->getTransportsKeys(); 
            // returns ['database']
            \Weble\JoomlaQueues\Message\PingMessage::class      => [
                [
                    'handler'    => \Weble\JoomlaQueues\Handler\PingHandler::class,
                    'transports' => $transports
                    // 'method' => 'otherMethod' // different method on the handler, other than __invoke()
                ]
            ]
        ];
    }
```

#### 2. ```onGetQueueHandlers``` 

Add a ```onGetQueueHandlers```  method, and return an associative array of handlers with an optional configuration.
Check the Email Handler plugin as an example:

```php
    public function onGetQueueHandlers()
    {
        return [
           \Weble\JoomlaQueues\Handler\SendEmailHandler::class => [
               "handles" => [
                    \Weble\JoomlaQueues\Message\SendEmailMessage::class
               ],
               // "bus" => FOF30\Container\Container::getInstance('com_queues')->bus->getDefaultName(),
               // "from_transport" => 'default' ,
               // "method" => "someOtherHandlerClassMethodInsteadOfInvoke",
               // "priority" => 0
           ],
           // this one self implements through the MessageSubscriberInterface
           \Weble\JoomlaQueues\Handler\PingHandler::class
        ];
    }
```

## Dispatching Messages

In order to dispatch new messages, you just need to call the ```dispatch``` method in the ```queue``` service provider through the container.

```php
\FOF30\Container\Container::getInstance('com_queues')->queue->dispatch(new \Weble\JoomlaQueues\Message\PingMessage());
```

## Consuming Messages (running jobs)

This is done through a CLI command

```php bin/console messenger:consume {optionalTransportName}```
