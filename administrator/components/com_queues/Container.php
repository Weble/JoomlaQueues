<?php


namespace Weble\JoomlaQueues\Admin;

use Weble\JoomlaQueues\Admin\Service\Bus;
use Weble\JoomlaQueues\Admin\Service\Configuration;
use Weble\JoomlaQueues\Admin\Service\Handler;
use Weble\JoomlaQueues\Admin\Service\Queue;
use Weble\JoomlaQueues\Admin\Service\Transport;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

/**
 * @property-read Queue $queue
 * @property-read Bus $bus
 * @property-read Transport $transport
 * @property-read Handler $handler
 * @property-read Configuration $queueConfig
 */
class Container extends \FOF30\Container\Container
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        if (!isset($this['queueConfig'])) {
            $this['queueConfig'] = function (self $c) {
                return new Configuration($c);
            };
        }

        if (!isset($this['transport'])) {
            $this['transport'] = function (self $c) {
                return new Transport($c);
            };
        }

        if (!isset($this['handler'])) {
            $this['handler'] = function (self $c) {
                return new Handler($c);
            };
        }

        if (!isset($this['bus'])) {
            $this['bus'] = function (self $c) {
                return new Bus($c);
            };
        }

        if (!isset($this['queue'])) {
            $this['queue'] = function (self $c) {
                return new Queue($c);
            };
        }

    }
}
