<?php


namespace Weble\JoomlaQueues\Admin;


use Enqueue\Dbal\DbalConnectionFactory;
use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use Joomla\CMS\Factory;

/**
 * @property-read ConnectionFactory $queueFactory
 * @property-read Context $queueContext
 */
class Container extends \FOF30\Container\Container
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        // Platform abstraction service
        if (!isset($this['queueContext'])) {
            $this['queueClient'] = function (self $c) {
                return $c->queueFactory->createContext();
            };
        }

        // Platform abstraction service
        if (!isset($this['queueFactory'])) {
            $this['queueFactory'] = function (self $c) {
                $config = Factory::getConfig();
                $user = $config->get("user");
                $password = $config->get("password");
                $host = $config->get("host");
                $db = $config->get("db");
                $url = "mysql://" . $user . ":" . $password . "@" . $host . "/" . $db;

                $config = [
                    'connection' => [
                        'url'    => $url,
                        'driver' => 'pdo_mysql',
                    ],
                    'table_name' => $config->get("dbprefix") . "queues_jobs",
                ];

                return new DbalConnectionFactory($config);
            };
        }
    }
}
