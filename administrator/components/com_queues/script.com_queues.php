<?php
// no direct access
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Enqueue\Dbal\DbalConnectionFactory;
use Joomla\CMS\Factory;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;

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
        $this->getDriverConnection()->setup();
    }

    private function installQueuesTable()
    {
        $tableName = Factory::getConfig()->get("dbprefix") . "queues_queues";
        $sm = $this->getDbalConnection()->getSchemaManager();

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

    private function getDbalConnection(): \Doctrine\DBAL\Connection
    {
        $config = Factory::getConfig();

        $connectionParams = array(
            'dbname'   => $config->get('db'),
            'user'     => $config->get('user'),
            'password' => $config->get('password'),
            'host'     => $config->get('host'),
            'driver'   => 'pdo_mysql',
        );

        $conn = DriverManager::getConnection($connectionParams);
        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        return $conn;
    }

    private function getDriverConnection(): Connection
    {
        $config = Factory::getConfig();

        $driverConnection = new Connection([
            'table_name'        => $config->get('dbprefix') . 'queues_jobs',
            'redeliver_timeout' => 3600,
            'auto_setup'        => false,
        ], $this->getDbalConnection());

        return $driverConnection;
    }

}
