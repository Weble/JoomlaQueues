# Joomla Queues

Implementation of symfony/messenger for Joomla! to run background commands using queues.
See [https://symfony.com/doc/current/messenger.html](https://symfony.com/doc/current/messenger.html)

# WARNING: This is still in development. Use at your own risk, and feel free to PR any changes you see fit!

## Requirements

First, you need to have our Joomla Commands package installed:
[https://github.com/Weble/JoomlaCommands](https://github.com/Weble/JoomlaCommands)
After that you can write messages and handlers to dispatched and run through a queue.

This also requires php 7.2.5+ due to its Symfony's dependencies. It's tested on php 7.3 / 7.4.

## Installation

We don't yet provide an installable package.
This repository mimics the standard Joomla! installation structure, so just by downloading the master branch into your project should make it available for installation through the standard Joomla! discover installer.

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

## Advanced Configurations

Other than the basic usage, you can extensively customize other parts of the process as well.

### Adding Transports

You can add more transports (like redis, sqs, etc) through Joomla! plugins as well.
This is done using the ```onGetQueueTransports``` method.

This method should return an array of objects that implements the ```\Weble\JoomlaQueues\Transport\ProvidesTransport``` interface. This class describes the transport itself, and its accessory configuration, like retry strategy and serializer.

In addition, we provide an abstract ```\Weble\JoomlaQueues\Transport\TransportProvider```  class, that implements a standard serializer and retry strategy for you. The only requirement for you is to implement the ```transport()``` method, that should return a ```Symfony\Component\Messenger\Transport\TransportInterface```

Check the Default plugin and the ```DatabaseTransportProvider``` class for more informations, alongside the [symfony documentation on transports](https://symfony.com/doc/current/messenger.html#transport-configuration).

```php
    public function onGetQueueTransports()
    {
        return [
            new YourCustomTransportProvider(),
        ];
    }
```

If you just want to add more queues using our provided database transport, you can do it like this:

```php
    public function onGetQueueTransports()
    {
        return [
            new \Weble\JoomlaQueues\Transport\DatabaseTransportProvider('newqueuename'),
        ];
    }
```

### Multiple Buses

You can add more buses to process different type of requests. More on this on the [official symfony documentation](https://symfony.com/doc/current/messenger/multiple_buses.html).

You can do so by implementing the ```onGetQueueBuses``` method in your plugin.

This method should return an array of objects that implements the ```\Weble\JoomlaQueues\Bus\ProvidesBus``` interface. This class describes the bus itself, and its accessory configuration, like middlewares to be used.

In addition, we provide an abstract ```\Weble\JoomlaQueues\Bus\BusProvider```  class, that implements a standard list of middlewares. The only requirement for you is to implement the ```bus()``` method, that should return a ```Symfony\Component\Messenger\MessageBusInterface```

Check the Default plugin and the ```DefaultBusProvider``` class for more informations.

```php
    public function onGetQueueBuses()
   {
       return [
           new YourCustomBusProvider()
       ];
   }
```
