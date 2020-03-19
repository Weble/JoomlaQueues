<?php
// no direct access
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Enqueue\Dbal\DbalConnectionFactory;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

// Load FOF
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php')) {
    return;
}

require_once JPATH_LIBRARIES . '/joomla-queues/vendor/autoload.php';

class Com_QueuesInstallerScript
{
    public function postflight($type, $parent)
    {
        $this->installJobsTable();
        $this->installQueuesTable();
    }

    private function installJobsTable()
    {
        $this->getDbalFactory()->createContext()->createDataBaseTable();
    }

    private function installQueuesTable()
    {
        $tableName = Factory::getConfig()->get("dbprefix") . "queues_queues";
        $sm = $this->getDbalFactory()->createContext()->getDbalConnection()->getSchemaManager();

        if ($sm->tablesExist([$tableName])) {
            return;
        }

        $table = new Table($tableName);

        $table->addColumn('id', Type::BIGINT)->setAutoincrement(true);
        $table->addColumn('name', Type::STRING);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['name']);

        $sm->createTable($table);
    }

    private function getDbalFactory(): DbalConnectionFactory
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

        return new DbalConnectionFactory($config);
    }

}
