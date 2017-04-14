<?php

namespace LingORM\Drivers\Mysql;

class PDOMysql
{
    private $dbHost;
    private $dbUserName;
    private $dbPassword;
    private $dbDatabase;
    private $dbCharset;
    private $dbConnection;

    public function __construct($databaseInfo)
    {
        $this->getConfig($databaseInfo);
        $this->connect();
    }

    private function getConfig($databaseInfo)
    {
        $dbinfo = $databaseInfo;
        $this->dbHost = $dbinfo["host"];
        if(empty($this->dbHost))
        {
            $this->dbHost = "127.0.0.1";
        }
        $this->dbUserName = $dbinfo["user"];
        $this->dbPassword = $dbinfo["password"];
        $this->dbDatabase = $dbinfo["database"];
        $this->dbCharset = $dbinfo["charset"];
        if(! $this->dbCharset)
        {
            $this->dbCharset = "UTF8";
        }
    }

    private function connect()
    {
        $this->dbConnection = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbDatabase . ';charset=' . $this->dbCharset, $this->dbUserName, $this->dbPassword);
        
        $this->dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    private function prepareSql($sql, $paramArr)
    {
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