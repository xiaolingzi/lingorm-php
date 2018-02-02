<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\DatabaseConfig;
class PDOMysql
{
    private $dbHost;
    private $dbUserName;
    private $dbPassword;
    private $dbDatabase;
    private $dbCharset;
    
    private $dbConnection;
    private $readDatabase;
    private $writeDatabase;

    public function __construct($databaseInfo)
    {
        
        if(!array_key_exists("servers", $databaseInfo))
        {
        	$this->readDatabase = $databaseInfo;
        	$this->writeDatabase = $databaseInfo;
        }
        else 
        {
        	$dbConfig = new DatabaseConfig();
        	$this->readDatabase = $dbConfig->getReadWriteDatabaseInfo($databaseInfo, "r");
        	$this->writeDatabase = $dbConfig->getReadWriteDatabaseInfo($databaseInfo, "w");
        }
        
        $this->readDatabase = $this->getConfig($this->readDatabase);
        $this->writeDatabase = $this->getConfig($this->writeDatabase);
        
    }

    private function getConfig($databaseInfo)
    {
        if(empty($databaseInfo["host"]))
        {
            $databaseInfo["host"] = "127.0.0.1";
        }

        if(empty($databaseInfo["charset"]))
        {
            $databaseInfo["charset"] = "UTF8";
        }
        
        return $databaseInfo;
    }

    private function connect($databaseInfo)
    {
        $this->dbConnection = new \PDO('mysql:host=' . $databaseInfo["host"] . ';dbname=' . $databaseInfo["database"] . ';charset=' . $databaseInfo["charset"], $databaseInfo["user"], $databaseInfo["password"]);
        
        $this->dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    private function prepareSql($sql, $paramArr)
    {   
        $sql = trim($sql);
        
        if(strtolower(substr($sql, 0, 6)) == "select")
        {
        	$this->connect($this->readDatabase);
        }
        else 
        {
            $this->connect($this->writeDatabase);
        }
        
        $statement = $this->dbConnection->prepare($sql);
        $statement->execute($paramArr);
        return $statement;
    }

    private function getLastInsertId()
    {
        return $this->dbConnection->lastInsertId();
    }

    public function fetchOne($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    public function fetchAll($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }
    
    public function insert($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $this->getLastInsertId();
        $statement->closeCursor();
        return $result;
    }

    public function excute($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $statement->rowCount();
        $statement->closeCursor();
        return $result;
    }
}

?>