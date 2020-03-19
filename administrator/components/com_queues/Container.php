<?php


namespace Weble\JoomlaQueues\Admin;


use Weble\JoomlaQueues\Service\Queue;

/**
 * @property-read Queue $queue
 */
class Container extends \FOF30\Container\Container
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        if (!isset($this['queue'])) {
            $this['queue'] = function (self $c) {
                return new Queue($c);
            };
        }
    }
}
