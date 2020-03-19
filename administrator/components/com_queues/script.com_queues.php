<?php
// no direct access
use Enqueue\Dbal\DbalConnectionFactory;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

require_once JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php';

class Com_QueuesInstallerScript
{
    public function postflight($type, $parent)
    {
        $this->installJobsTable();
    }

    private function installJobsTable()
    {
        $this->getDbalContext()->createDataBaseTable();
    }

    private function getDbalContext()
    {
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

        $factory = new DbalConnectionFactory($config);

        return $factory->createContext();
    }
}
