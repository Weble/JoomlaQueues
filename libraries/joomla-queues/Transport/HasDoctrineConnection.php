<?php


namespace Weble\JoomlaQueues\Transport;


use Doctrine\DBAL\DriverManager;
use Joomla\CMS\Factory;

trait HasDoctrineConnection
{
    protected $dbConnection;

    protected function dbConnection(): \Doctrine\DBAL\Connection
    {
        if (!$this->dbConnection) {
            $config = Factory::getConfig();
            $connectionParams = array(
                'dbname'   => $config->get('db'),
                'user'     => $config->get('user'),
                'password' => $config->get('password'),
                'host'     => $config->get('host'),
                'driver'   => 'pdo_mysql',
            );
            $dbConnection = DriverManager::getConnection($connectionParams);
            $dbConnection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            $this->dbConnection = $dbConnection;
        }

        return $this->dbConnection;
    }
}
