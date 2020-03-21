<?php


namespace Weble\JoomlaQueues\Admin;

use Weble\JoomlaQueues\Admin\Service\Bus;
use Weble\JoomlaQueues\Admin\Service\Queue;
use Weble\JoomlaQueues\Admin\Service\RoutableBus;
use Weble\JoomlaQueues\Admin\Service\Transport;

require_once(JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php');

/**
 * @property-read Queue $queue
 * @property-read Bus $bus
 * @property-read Transport $transport
 */
class Container extends \FOF30\Container\Container
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

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

        if (!isset($this['transport'])) {
            $this['transport'] = function (self $c) {
                return new Transport($c);
            };
        }
    }
}
