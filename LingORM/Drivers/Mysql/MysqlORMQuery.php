<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Mapping\DocParser;
use LingORM\Drivers\AbstractWhereExpression;
use LingORM\Drivers\AbstractOrderExpression;
use LingORM\Drivers\IORMQuery;

class MysqlORMQuery implements IORMQuery
{
    private $_pdoMysql;

    public function __construct($databaseInfo)
    {
        $this->_pdoMysql = new PDOMysql($databaseInfo);
    }

    public function fetchOne($table, AbstractWhereExpression $where, AbstractOrderExpression $order = null)
    {
        if(empty($where))
        {
            throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(! empty($table->__database))
        {
            $tableName = $table->__database . "." . $tableName;
        }
        
        $sql = "select * from " . $tableName . " " . $table->__alias_table_name . " where " . $where->sql;
        if(! empty($order) && ! empty($order->sql))
        {
            $sql .= " order by " . $order->sql;
        }
        $sql .= " limit 1";
        
        $tempResult = $this->_pdoMysql->fetchOne($sql, $where->params);
        $parser = new DocParser($table);
        $result = $parser->getObjectFromArray($tempResult);
        return $result;
    }

    public function fetchAll($table, AbstractWhereExpression $where, AbstractOrderExpression $order = null, $top = 0)
    {
        if(empty($where))
        {
            throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(! empty($table->__database))
        {
            $tableName = $table->__database . "." . $tableName;
        }
        
        $sql = "select * from " . $tableName . " " . $table->__alias_table_name . " where " . $where->sql;
        if(! empty($order) && ! empty($order->sql))
        {
            $sql .= " order by " . $order->sql;
        }
        
        $top = intval($top);
        if($top > 0)
        {
            $sql .= " order by " . $order->sql . " limit " . $top;
        }
        
        $tempResult = $this->_pdoMysql->fetchAll($sql, $where->params);
        $result = array();
        $parser = new DocParser($table);
        if(! empty($tempResult))
        {
            for($i = 0; $i < count($tempResult); $i ++)
            {
                $entity = $parser->getObjectFromArray($tempResult[$i]);
                array_push($result, $entity);
            }
        }
        return $result;
    }

    public function insert($entity)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        
        $tableName = $table->name;
        if(! empty($table->database))
        {
            $tableName = $table->database . "." . $tableName;
        }
        $fieldStr = "";
        $valueStr = "";
        $paramArr = array();
        $index=0;
        foreach($table->fieldArr as $field)
        {
            if($field->isGenerated)
            {
                continue;
            }
            $tempFieldName="f".$index;
            $fieldStr .= $field->name . ",";
            $valueStr .= ":" . $tempFieldName . ",";
            $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            $index++;
        }
        $fieldStr = trim($fieldStr, ",");
        $valueStr = trim($valueStr, ",");
        
        $sql = "insert into $tableName ($fieldStr) values ($valueStr)";
        
        return $this->_pdoMysql->insert($sql, $paramArr);
    }

    public function batchInsert($entityArr, $nullIgnore=FALSE)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(! empty($table->database))
        {
            $tableName = $table->database . "." . $tableName;
        }
        
        $fieldStr = "";
        $insertFieldArr = array();
        foreach ($table->fieldArr as $field)
        {
            if($field->isGenerated)
            {
                continue;
            }
            if($nullIgnore && is_null($field->value))
            {
                continue;
            }
            array_push($insertFieldArr, $field->name);
        	$fieldStr.=$field->name.",";
        }
        $fieldStr = trim($fieldStr,",");
        
        $valueStr = "";
        $paramArr = array();
        $index=0;
        for($i=0;$i<count($entityArr);$i++)
        {
            $parser = new DocParser($entityArr[$i]);
            $table = $parser->getTable();
            $tempValueStr = "";
            
            foreach ($table->fieldArr as $field)
            {
                if($field->isGenerated)
                {
                    continue;
                }
                if($nullIgnore && !in_array($field->name, $insertFieldArr))
                {
                    continue;
                }
            	
            	if(is_null($field->value))
            	{
            	    $tempValueStr.="default,";
            	}
            	else 
            	{
            	    $tempFieldName = "f".$index;
            	    $tempValueStr.=":".$tempFieldName.",";
            	    $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            	    $index++;
            	}
//             	$tempValueStr.=":".$tempFieldName.",";
//             	$paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            	
            }
            $tempValueStr = trim($tempValueStr,",");
            $valueStr .= "(".$tempValueStr."),";
        }
        $valueStr = trim($valueStr,",");
        
        $sql = "insert into $tableName ($fieldStr) values $valueStr";
        
        return $this->_pdoMysql->excute($sql, $paramArr);
    }

    public function update($entity, $nullIgnore = FALSE)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(! empty($table->database))
        {
            $tableName = $table->database . "." . $tableName;
        }
        
        $setStr = "";
        $whereStr = "";
        $paramArr = array();
        $index=0;
        
        foreach($table->fieldArr as $field)
        {
            if($field->primaryKey)
            {
                $tempFieldName = "p".$index;
                if(empty($whereStr))
                {
                	$whereStr = $field->name. "=:".$tempFieldName; 
                }
                else 
                {
                    $whereStr .= " and ".$field->name. "=:".$tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            }
            
            if($field->isGenerated)
            {
                continue;
            }
            if($nullIgnore && is_null($field->value))
            {
                continue;
            }
            $tempFieldName = "f".$index;
            $setStr .= $field->name. "=:".$tempFieldName.","; 
            $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            $index++;
        }
        $setStr = trim($setStr,",");
        
        if(empty($whereStr))
        {
        	throw new \Exception("The 'update' method require at least one primary Key");
        }
        
        $sql = "update $tableName set $setStr where $whereStr";
        
        return $this->_pdoMysql->excute($sql, $paramArr);
    }

    /**
     * only for one primary key table
     */
    public function batchUpdate($entityArr, $nullIgnore = FALSE)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(! empty($table->database))
        {
            $tableName = $table->database . "." . $tableName;
        }
        
        $idCount = 0;
        $idPropertyName = "";
        $idFieldName = "";
        foreach ($table->fieldArr as $key => $field)
        {
        	if($field->primaryKey)
        	{
        	    $idFieldName = $field->name;
        	    $idPropertyName = $key;
        		$idCount++;
        	}
        }
        if($idCount>1)
        {
        	throw new \Exception("This method applies only to tables that have only one primary key field");
        }
        
        $idStr = "";
        $paramArr = array();
        $setArr = array();
        $index = 0;
        for($i=0;$i<count($entityArr);$i++)
        {
            $parser = new DocParser($entityArr[$i]);
            $table = $parser->getTable();
            
            $primaryKeyName = "p".$i;
            $paramArr[$primaryKeyName] = $entityArr[$i]->{$idPropertyName};
            $idStr.=":".$primaryKeyName.",";
            
            foreach($table->fieldArr as $key => $field)
            {
                if($field->primaryKey)
                {
                    continue;
                }
                if($field->isGenerated)
                {
                    continue;
                }
                
                if($nullIgnore && is_null($field->value))
                {
                	continue;
                }
                
                $tempIdName = "d".$index;
                $paramArr[$tempIdName] = $entityArr[$i]->{$idPropertyName};
                
                $tempFieldName = "f".$index;
                if(! array_key_exists($field->name, $setArr))
                {
                    $setArr[$field->name] = " when :".$tempIdName." then :".$tempFieldName;
                }
                else 
                {
                    $setArr[$field->name] .= " when :".$tempIdName." then :".$tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
                
                $index++;
            }
        }
        $idStr = trim($idStr, ',');
        
        $setStr = "";
        foreach ($setArr as $key=>$value)
        {
        	$setStr.="$key = case $idFieldName $value else $key end,";
        }
        
        $setStr = trim($setStr, ',');
        
        $sql = "update $tableName set $setStr where $idFieldName in($idStr)";
        
        return $this->_pdoMysql->excute($sql, $paramArr);
    }

    /**
     * get the field value from the propert value of the entity
     * 
     * @param unknown $originalValue            
     * @param string $type            
     * @return string unknown
     */
    private function getFieldValue($originalValue, $type)
    {
        if($type == "datetime")
        {
            if(gettype($originalValue) == "object" && get_class($originalValue) == "DateTime")
            {
                return $originalValue->format("Y-m-d H:i:s");
            }
            else if(gettype($originalValue) == "integer")
            {
                return date("Y-m-d H:i:s", $originalValue);
            }
        }
        return $originalValue;
    }

    public function updateBy($table, $setParamArr, AbstractWhereExpression $where)
    {
        if(empty($setParamArr))
        {
            throw new \Exception("No field for update.");
        }
        if(empty($where))
        {
            throw new \Exception("Missing the where condition!");
        }
        
        $setSql = "";
        for($i = 0; $i < count($setParamArr); $i ++)
        {
            $tempSql = "";
            if(gettype($setParamArr[$i]) == "string")
            {
                $tempSql = $setParamArr[$i];
            }
            else
            {
                $expression = MysqlDefine::getExpression($setParamArr[$i], $where->params);
                $where->params = $expression["params"];
                $tempSql = $expression["sql"];
            }
            
            if($i == 0)
            {
                $setSql = $tempSql;
            }
            else
            {
                $setSql .= ", " . $tempSql;
            }
        }
        
        $tableName = $table->__table_name;
        if(! empty($table->__database))
        {
            $tableName = $table->__database . "." . $tableName;
        }
        
        $sql = "update " . $tableName . " " . $table->__alias_table_name . " set " . $setSql . " where " . $where->sql;
        
        $result = $this->_pdoMysql->excute($sql, $where->params);
        return $result;
    }

    public function delete($entity)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(! empty($table->database))
        {
            $tableName = $table->database . "." . $tableName;
        }
        
        $whereStr = "";
        $paramArr = array();
        $index=0;
        
        foreach($table->fieldArr as $field)
        {
            if($field->primaryKey)
            {
                $tempFieldName = "p".$index;
                if(empty($whereStr))
                {
                	$whereStr = $field->name. "=:".$tempFieldName; 
                }
                else 
                {
                    $whereStr .= " and ".$field->name. "=:".$tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
                $index++;
            }
        }
        
        $sql = "delete from $tableName where $whereStr";
        return $this->_pdoMysql->excute($sql, $paramArr);
    }

    public function deleteBy($table, AbstractWhereExpression $where)
    {
        if(empty($where))
        {
            throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(! empty($table->__database))
        {
            $tableName = $table->__database . "." . $tableName;
        }
        
        $sql = "delete " . $table->__alias_table_name . " from " . $tableName . " " . $table->__alias_table_name . " where " . $where->sql;
        
        $result = $this->_pdoMysql->excute($sql, $where->params);
        return $result;
    }
}