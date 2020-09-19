<?php
namespace LingORM;

use LingORM\Drivers\DatabaseConfig;
use LingORM\Drivers\Mysql\MysqlQuery;
use LingORM\Drivers\Sqlite\SqliteQuery;

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
            case "sqlite":
                return new SqliteQuery($databaseInfo);
            case "sqlite3":
                return new SqliteQuery($databaseInfo);
            default:
                return new MysqlQuery($databaseInfo);
        }
    }

    public static function config($configFile)
    {
        DatabaseConfig::$configFile = $configFile;
    }

}
