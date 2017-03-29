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

    public function insert($tableName, $paramArr)
    {
        if(empty($paramArr))
        {
            throw new \Exception("Nothing to be updated.");
        }
        $columns = "";
        $values = "";
        foreach($paramArr as $key => $value)
        {
            $columns .= $key . ",";
            $values .= ":" . $key . ",";
        }
        $columns = trim($columns, ',');
        $values = trim($values, ',');
        $sql = "insert into $tableName ($columns) values ($values)";
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }

    public function batchInsert($tableName, $entityArr)
    {
        if(empty($entityArr))
        {
            throw new \Exception("Nothing to be inserted.");
        }
        
        $columns = "";
        $values = "";
        $dataArr = array();
        foreach($entityArr[0] as $key => $value)
        {
            $columns .= $key . ",";
        }
        for($i = 0; $i < count($entityArr); $i ++)
        {
            $values .= "(";
            $tempValue = "";
            foreach($entityArr[$i] as $key => $value)
            {
                $tempKey = $key . "_" . $i;
                $tempValue .= ":" . $tempKey . ",";
                $dataArr[$tempKey] = $value;
            }
            $tempValue = trim($tempValue, ',');
            $values .= $tempValue . "),";
        }
        $columns = trim($columns, ',');
        $values = trim($values, ',');
        $sql = "insert into $tableName ($columns) values $values";
        
        $statement = $this->prepareSql($sql, $dataArr);
        $result = $this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }

    public function updateById($tableName, $paramArr, $idArr)
    {
        if(empty($paramArr))
        {
            throw new \Exception("Nothing to be updated.");
        }
        if(empty($idArr))
        {
            throw new \Exception("The id array can not be null.");
        }
        
        $setStr = "";
        foreach($paramArr as $key => $value)
        {
            if(array_key_exists($key, $idArr))
            {
            	continue;
            }
            $setStr .= $key . "=:" . $key . ",";
        }
        $setStr = trim($setStr, ',');
        
        $whereStr="";
        $index=0;
        foreach ($idArr as $key=>$value)
        {
            if($value==NULL)
            {
                throw new \Exception("The id can not be null.");
            }
            if($index==0)
            {
                $whereStr = $key . "=:" . $key;
            }
            else
            {
                $whereStr .= " and ".$key . "=:" . $key;
            }
            $index++;
            $paramArr[$key]=$value;
        }
        
        $sql = "update $tableName set $setStr where $whereStr";
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $statement->rowCount();
        $statement->closeCursor();
        return intval($result);
    }

    public function batchUpdateById($tableName, $entityArr, $idFieldName)
    {
        if(! is_array($entityArr))
        {
            return 0;
        }
        
        $inStr = "";
        $fieldSetArr = array();
        $dataArr = array();
        
        for($i = 0; $i < count($entityArr); $i ++)
        {
            $inStr .= $entityArr[$i][$idFieldName] . ",";
            foreach($entityArr[$i] as $key => $value)
            {
                if($key==$idFieldName)
                {
                    continue;
                }
                $tempKey = $key . "_" . $i;
                $dataArr[$tempKey] = $value;
                if(! array_key_exists($key, $fieldSetArr))
                {
                    $fieldSetArr[$key] = " when " . $entityArr[$i][$idFieldName] . " then :" . $tempKey . " ";
                }
                else
                {
                    $fieldSetArr[$key] .= "when " . $entityArr[$i][$idFieldName] . " then :" . $tempKey . " ";
                }
            }
        }
        
        $setStr = "";
        foreach($fieldSetArr as $key => $value)
        {
            $setStr .= "$key = case $idFieldName $value else $key end,";
        }
        $setStr = trim($setStr, ',');
        $inStr = trim($inStr, ',');
        
        $sql = "update $tableName set $setStr where $idFieldName in($inStr)";
        $statement = $this->prepareSql($sql, $dataArr);
        $result = $this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }

    public function updateByCondition($tableName, $paramArr, $whereStr)
    {
        if(! is_array($paramArr))
        {
            return 0;
        }
        
        $setStr = "";
        foreach($paramArr as $key => $value)
        {
            $setStr .= $key . "=:" . $key . ",";
        }
        $setStr = trim($setStr, ',');
        
        $sql = "update $tableName set $setStr where $whereStr";
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $this->getLastInsertId();
        $statement->closeCursor();
        return intval($result);
    }

    public function deleteById($tableName, $idArr)
    {
        if(empty($idArr))
        {
            throw new \Exception("The id array can not be null.");
        }
        
        $whereStr="";
        $index=0;
        $paramArr=array();
        foreach ($idArr as $key=>$value)
        {   if($value==NULL)
            {
                throw new \Exception("The id can not be null.");
            }
            if($index==0)
            {
                $whereStr = $key . "=:" . $key;
            }
            else
            {
                $whereStr .= " and ".$key . "=:" . $key;
            }
            $index++;
            $paramArr[$key]=$value;
        }
        
        $sql = "delete from $tableName where $whereStr";
        $statement = $this->prepareSql($sql, $paramArr);
        $result = $statement->rowCount();
        $statement->closeCursor();
        return intval($result);
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