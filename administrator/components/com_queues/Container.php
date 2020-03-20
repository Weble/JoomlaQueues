<?php


namespace Weble\JoomlaQueues\Admin;

use Weble\JoomlaQueues\Service\DefaultBus;
use Weble\JoomlaQueues\Service\Queue;
use Weble\JoomlaQueues\Service\RoutableBus;

/**
 * @property-read Queue $queue
 * @property-read DefaultBus $defaultBus
 * @property-read RoutableBus $routableBus
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
        if (!isset($this['defaultBus'])) {
            $this['defaultBus'] = function (self $c) {
                return new DefaultBus($c);
            };
        }
        if (!isset($this['routableBus'])) {
            $this['routableBus'] = function (self $c) {
                return new RoutableBus($c);
            };
        }
    }
}
