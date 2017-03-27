<?php
namespace LingORM\ORM;

use LingORM\Drivers\DatabaseConfig;
use LingORM\Drivers\Mysql\MysqlWhereExpression;
use LingORM\Drivers\Mysql\MysqlOrderExpression;
use LingORM\Mapping\Field;
use LingORM\Mapping\DocParser;
use LingORM\Drivers\Mysql\MysqlORMQuery;
use LingORM\Drivers\Mysql\MysqlQuery;
use LingORM\Config;
use LingORM\Drivers\Mysql\MysqlQueryBuilder;

class Query
{
    private $_databaseInfo;
    private $_index=0;
    
    /**
     * @param string $key
     * database config key
     */
    public function __construct($key=null)
    {
        if(!empty($key))
        {
        	$this->_databaseInfo=(new DatabaseConfig())->getDatabaseInfoByKey($key);
        }
    }
    
    /**
     * @param class $classInstance
     * @param string $aliasTableName
     * @param string $database
     * @throws \Exception
     * @return class object
     */
    public function createTable($entity, $aliasTableName=null)
    {
        $table=(new DocParser($entity))->getTable();
        if(empty($table) || empty($table->fieldArr))
        {
            throw new \Exception("The entity class is not valid!");
        }
        
        if(empty($this->_databaseInfo))
        {
            if(empty($table->database))
            {
                $this->_databaseInfo=(new DatabaseConfig())->getDatabaseInfoByKey(Config::DEFAULT_DATABASE_SERVER);
            }
            else 
            {
                $this->_databaseInfo=(new DatabaseConfig())->getDatabaseInfoByDatabase($table->database);
            }
        }
        
        if(empty($this->_databaseInfo))
        {
            throw new \Exception("Missing database configuration!");
        }
        
        if(empty($table->database))
        {
            $table->database = $this->_databaseInfo["database"];
        }
        
        $tableName=$table->name;
        $entity->{"__table_name"}=$tableName;
        
        if(empty($aliasTableName))
        {
        	$aliasTableName="t".$this->_index++;
        }
        $entity->{"__alias_table_name"}=$aliasTableName;
        
        $entity->{"__database"}=$table->database;
        
        $entity->{"__fieldArr"}=$table->fieldArr;
        
        foreach ($table->fieldArr as $key=>$value)
        {
            $field=new Field();
            $field->tableName=$tableName;
            $field->aliasTableName=$aliasTableName;
            $field->fieldName=$value->name;
            $field->aliasFieldName=null;
            $entity->{$key}=$field;
        }
        return $entity;
    }
    
    public function createQuery()
    {
        switch ($this->_databaseInfo["driver"])
        {
        	case "pdo_mysql":
        	    return new MysqlORMQuery($this->_databaseInfo);
        	default:
        	    return new MysqlORMQuery($this->_databaseInfo);
        }
    }
    
    public function createQueryBuilder()
    {
        switch ($this->_databaseInfo["driver"])
        {
        	case "pdo_mysql":
        	    return new MysqlQueryBuilder($this->_databaseInfo);
        	default:
        	    return new MysqlQueryBuilder($this->_databaseInfo);
        }
    }
    
    public function createWhere()
    {
        switch ($this->_databaseInfo["driver"])
        {
        	case "pdo_mysql":
        	    return new MysqlWhereExpression();
        	default:
        	    return new MysqlWhereExpression();
        }
    }
    
    public function createOrder()
    {
        switch ($this->_databaseInfo["driver"])
        {
        	case "pdo_mysql":
        	    return new MysqlOrderExpression();
        	default:
        	    return new MysqlOrderExpression();
        }
    }
    
    public function createSql()
    {
        switch ($this->_databaseInfo["driver"])
        {
        	case "pdo_mysql":
        	    return new MysqlQuery($this->_databaseInfo);
        	default:
        	    return new MysqlQuery($this->_databaseInfo);
        }
    }
    
	
}