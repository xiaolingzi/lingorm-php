<?php

namespace LingORM\Drivers\Mysql;

class PDOMysql
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
        if (!array_key_exists("host", $databaseInfo) || empty($databaseInfo["host"])) {
            $databaseInfo["host"] = "127.0.0.1";
        }

        if (!array_key_exists("port", $databaseInfo) || empty($databaseInfo["port"])) {
            $databaseInfo["port"] = "3306";
        }

        if (!array_key_exists("charset", $databaseInfo) || empty($databaseInfo["charset"])) {
            $databaseInfo["charset"] = "UTF8";
        }

        return $databaseInfo;
    }

    private function connect($mode)
    {
        $dbConfig = new MysqlConfig();
        $databaseInfo = $dbConfig->getReadWriteDatabaseInfo($this->databaseInfo, $mode);
        $this->dbConnection = new \PDO('mysql:host=' . $databaseInfo["host"] . ';port=' . $databaseInfo["port"] . ';dbname=' . $databaseInfo["database"] . ';charset=' . $databaseInfo["charset"], $databaseInfo["user"], $databaseInfo["password"]);

        // $this->dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbConnection->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
        return $this->dbConnection;
    }

    private function prepareSql($sql, $paramArr, $mode = "")
    {
        $sql = trim($sql);
        if (!empty($this->_transactionKey) && array_key_exists($this->_transactionKey, self::$_transactionConnectors)) {
            $this->dbConnection = self::$_transactionConnectors[$this->_transactionKey];
        } else {
            if (empty($mode)) {
                if (strtolower(substr($sql, 0, 6)) == "select") {
                    $mode = MysqlConfig::MODE_READ;
                } else {
                    $mode = MysqlConfig::MODE_WRITE;
                }
            }
            $this->connect($mode);
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
        $statement = $this->prepareSql($sql, $paramArr, MysqlConfig::MODE_READ);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    public function fetchAll($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr, MysqlConfig::MODE_READ);
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    public function insert($sql, $paramArr)
    {
        $statement = $this->prepareSql($sql, $paramArr, MysqlConfig::MODE_WRITE);
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
        $connection = $this->connect(MysqlConfig::MODE_WRITE);
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
