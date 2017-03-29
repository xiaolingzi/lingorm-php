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

    public function fetchOne($table,AbstractWhereExpression $where,AbstractOrderExpression $order=null)
    {
        if(empty($where))
        {
        	throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(!empty($table->__database))
        {
            $tableName = $table->__database.".".$tableName;
        }
        
        $sql = "select * from " . $tableName ." ".$table->__alias_table_name." where ".$where->sql;
        if(!empty($order) && !empty($order->sql))
        {
        	$sql.=" order by ".$order->sql;
        }
        
        $tempResult = $this->_pdoMysql->fetchOne($sql, $where->params);
        $parser=new DocParser($table);
        $result=$parser->getObjectFromArray($tempResult);
        return $result;
    }
    
    
    public function fetchAll($table,AbstractWhereExpression $where,AbstractOrderExpression $order=null,$top=0)
    {
        if(empty($where))
        {
        	throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(!empty($table->__database))
        {
            $tableName = $table->__database.".".$tableName;
        }
        
        $sql = "select * from " . $tableName ." ".$table->__alias_table_name." where ".$where->sql;
        if(!empty($order) && !empty($order->sql))
        {
        	$sql.=" order by ".$order->sql;
        }
        
        $top=intval($top);
        if($top>0)
        {
            $sql.=" order by ".$order->sql." limit ".$top;
        }
        
        $tempResult = $this->_pdoMysql->fetchAll($sql, $where->params);
        $result=array();
        $parser=new DocParser($table);
        if(!empty($tempResult))
        {
        	for($i=0;$i<count($tempResult);$i++)
        	{
        		$entity=$parser->getObjectFromArray($tempResult[$i]);
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
        if(!empty($table->database))
        {
            $tableName = $table->database.".".$tableName;
        }
        $paramArr = $this->getInsertParams($table->fieldArr);
        return $this->_pdoMysql->insert($tableName, $paramArr);
    }

    public function batchInsert($entityArr)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(!empty($table->database))
        {
            $tableName = $table->database.".".$tableName;
        }
        $insertEntityArr = array();
        foreach($entityArr as $entity)
        {
            $parser = new DocParser($entity);
            $table = $parser->getTable();
            $paramArr = $this->getInsertParams($table->fieldArr);
            array_push($insertEntityArr, $paramArr);
        }
        
        return $this->_pdoMysql->batchInsert($tableName, $insertEntityArr);
    }
    
    /**
     * get all the fields inserted 
     * @param array $fieldArr
     */
    private function getInsertParams($fieldArr)
    {
        $paramArr = array();
        foreach($fieldArr as $field)
        {
            if($field->isGenerated)
            {
                continue;
            }
            $paramArr[$field->name] = $this->getFieldValue($field->value,$field->type);
        }
        return $paramArr;
    }

    public function update($entity)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(!empty($table->database))
        {
            $tableName = $table->database.".".$tableName;
        }
        $paramArr = array();
        
        $idArr = array();
        foreach($table->fieldArr as $field)
        {
            if($field->isId)
            {
                $idArr[$field->name] = $field->value;
            }
            if($field->isGenerated)
            {
                continue;
            }
            $paramArr[$field->name] = $this->getFieldValue($field->value,$field->type);
        }
        return $this->_pdoMysql->updateById($tableName, $paramArr, $idArr);
    }

    /**
     * only for one id table
     */
    public function batchUpdate($entityArr)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if(!empty($table->database))
        {
            $tableName = $table->database.".".$tableName;
        }
        $idFieldName = "";
        $updateEntityArr = array();
        foreach($entityArr as $entity)
        {
            $parser = new DocParser($entity);
            $table = $parser->getTable();
            $paramArr = array();
            foreach($table->fieldArr as $field)
            {
                if($field->isId && empty($idFieldName))
                {
                    $idFieldName=$field->name;
                }
                $paramArr[$field->name] = $this->getFieldValue($field->value,$field->type);
            }
            array_push($updateEntityArr, $paramArr);
        }
        
        return $this->_pdoMysql->batchUpdateById($tableName, $updateEntityArr, $idFieldName);
    }
    
    /**
     * get the field value from the propert value of the entity
     * @param unknown $originalValue
     * @param string $type
     * @return string|unknown
     */
    private function getFieldValue($originalValue,$type)
    {
        if($type=="datetime")
        {
        	if(gettype($originalValue)=="object" && get_class($originalValue)=="DateTime")
        	{
        		return $originalValue->format("Y-m-d H:i:s");
        	}
        	else if(gettype($originalValue)=="integer")
        	{
        		return date("Y-m-d H:i:s",$originalValue);
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
        
        $setSql="";
        for($i=0;$i<count($setParamArr);$i++)
        {
            $tempSql = "";
            if(gettype($setParamArr[$i]) == "string")
            {
                $tempSql = $setParamArr[$i];
            }
            else
            {
                $expression=MysqlDefine::getExpression($setParamArr[$i],$where->params);
                $where->params=$expression["params"];
                $tempSql = $expression["sql"];
            }
            
            if($i==0)
            {
                $setSql=$tempSql;
            }
            else
            {
                $setSql.=", ".$tempSql;
            }
        }
        
        $tableName = $table->__table_name;
        if(!empty($table->__database))
        {
            $tableName = $table->__database.".".$tableName;
        }
    
        $sql = "update ". $tableName . " " .$table->__alias_table_name." set ". $setSql ." where ".$where->sql;
        
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
        if(!empty($table->database))
        {
            $tableName = $table->database.".".$tableName;
        }
        $idArr = array();
        foreach($table->fieldArr as $field)
        {
            
            if($field->isId)
            {
                $idArr[$field->name] = $field->value;
            }
        }
        return $this->_pdoMysql->deleteById($tableName, $idArr);
    }
    
    
    public function deleteBy($table, AbstractWhereExpression $where)
    {
        if(empty($where))
        {
            throw new \Exception("Missing the where condition!");
        }
        
        $tableName = $table->__table_name;
        if(!empty($table->__database))
        {
            $tableName = $table->__database.".".$tableName;
        }
        
        $sql = "delete ".$table->__alias_table_name." from " . $tableName ." ".$table->__alias_table_name." where ".$where->sql;
        
        $result = $this->_pdoMysql->excute($sql, $where->params);
        return $result;
    }
    
}