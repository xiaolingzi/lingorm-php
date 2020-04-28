<?php
namespace LingORM;

use LingORM\Drivers\DatabaseConfig;
use LingORM\Drivers\Mysql\MysqlQuery;

class ORM
{
    public static function db($key)
    {
        $databaseInfo = (new DatabaseConfig())->getDatabaseInfoByKey($key);
        if (empty($databaseInfo)) {
            throw new \Exception("Database configuration miss!");
        }
        switch ($databaseInfo["driver"]) {
            case "mysql":
                return new MysqlQuery($databaseInfo);
            default:
                return new MysqlQuery($databaseInfo);
        }
    }

    public static function config($configFile)
    {
        DatabaseConfig::$configFile = $configFile;
    }

}
