<?php

namespace LingORM\Drivers\Sqlite;

class SqliteNative
{
    private $dbConnection;
    private $databaseInfo;
    private $_transactionKey = "";
    private static $_transactionConnectors = array();

    public function __construct($databaseInfo, $transactionKey = "")
    {
        $this->_transactionKey = $transactionKey;
        $this->databaseInfo = $this->getConfig($databaseInfo);
    }

    private function getConfig($databaseInfo)
    {
        if (!array_key_exists("file", $databaseInfo) || empty($databaseInfo["file"])) {
            throw new \Exception("Database file not found");
        }

        if (!array_key_exists("timeout", $databaseInfo) || empty($databaseInfo["timeout"])) {
            $databaseInfo["timeout"] = 5;
        }

        return $databaseInfo;
    }

    private function connect()
    {
        $this->dbConnection = new \PDO("sqlite:".$this->databaseInfo["file"]);

        // $this->dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbConnection->setAttribute(\PDO::ATTR_TIMEOUT, $this->databaseInfo["timeout"]);
        return $this->dbConnection;
    }

    private function prepareSql($sql, $paramArr)
    {
        $sql = trim($sql);
        if (!empty($this->_transactionKey) && array_key_exists($this->_transactionKey, self::$_transactionConnectors)) {
            $this->dbConnection = self::$_transactionConnectors[$this->_transactionKey];
        } else {
            $this->connect();
        }

        $statement = $this->dbConnection->prepare($sql);
        $statement->execute($paramArr);
        return $statement;
    }

    private function getLastInsertId()
    {
        if (!empty($this->_transactionKey) && array_key_exists($this->_transactionKey, self::$_transactionConnectors)) {
            $this->dbConnection = self::$_transactionConnectors[$this->_transactionKey];
        }
        return $this->dbConnection->lastInsertId();
    }

    public function fetchOne($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr, SqliteConfig::MODE_READ);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    public function fetchAll($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr, SqliteConfig::MODE_READ);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    public function insert($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr, SqliteConfig::MODE_WRITE);
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

    public function begin()
    {
        $key = microtime();
        $connection = $this->connect(SqliteConfig::MODE_WRITE);
        self::$_transactionConnectors[$key] = $connection;
        self::$_transactionConnectors[$key]->beginTransaction();
        return $key;
    }

    public function commit()
    {
        if (array_key_exists($this->_transactionKey, self::$_transactionConnectors)) {
            self::$_transactionConnectors[$this->_transactionKey]->commit();
        } else {
            throw new \Exception("Begin a transaction first before commit");
        }
        unset(self::$_transactionConnectors[$this->_transactionKey]);
    }

    public function rollback()
    {
        if (array_key_exists($this->_transactionKey, self::$_transactionConnectors)) {
            self::$_transactionConnectors[$this->_transactionKey]->rollback();
        } else {
            throw new \Exception("Begin a transaction first before rollback");
        }
        unset(self::$_transactionConnectors[$this->_transactionKey]);
    }
}
