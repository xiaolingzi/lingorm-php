<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractQueryBuilder;
use LingORM\Drivers\AbstractWhereExpression;
use LingORM\Mapping\Field;
use LingORM\Mapping\DocParser;

class MysqlQueryBuilder extends AbstractQueryBuilder
{
    private $_pdoMysql;

    public function __construct($databaseInfo)
    {
        $this->_pdoMysql = new PDOMysql($databaseInfo);
    }

    public function select($colomnArgs)
    {
        $args = func_get_args();
        if(empty($args))
        {
            $this->selectSql = "*";
            return $this;
        }
        
        for($i = 0; $i < count($args); $i ++)
        {
            if(empty($args[$i]))
            {
                continue;
            }
            
            $fieldStr = $args[$i];
            
            if($args[$i] instanceof Field)
            {
                $fieldStr = $args[$i]->aliasTableName . "." . $args[$i]->fieldName;
                if($args[$i]->isDistinct == 1)
                {
                    $fieldStr = "distinct " . $fieldStr;
                }
                if($args[$i]->isCount == 1)
                {
                    $fieldStr = "count(" . $fieldStr . ")";
                }
                if($args[$i]->isSum == 1)
                {
                    $fieldStr = "sum(" . $fieldStr . ")";
                }
                if(! empty($args[$i]->aliasFieldName))
                {
                    $fieldStr = $fieldStr . " as " . $args[$i]->aliasFieldName;
                }
            }
            
            else if(gettype($args[$i]) == "object" && property_exists($args[$i], "__alias_table_name"))
            {
                $fieldStr = $args[$i]->__alias_table_name . ".*";
            }
            
            if($i == 0)
            {
                $this->selectSql = $fieldStr;
            }
            else
            {
                $this->selectSql .= "," . $fieldStr;
            }
        }
        return $this;
    }

    public function from($table)
    {
        $this->fromSql = $table->__database.".".$table->__table_name . " " . $table->__alias_table_name;
        return $this;
    }

    public function leftJoin($table, AbstractWhereExpression $where)
    {
        if(empty($this->params))
        {
            $this->params = $where->params;
        }
        else if(! empty($where->params))
        {
            $this->params = array_merge($this->params, $where->params);
        }
        
        $onSql = $where->sql;
        $tableName = $table->__database . "." . $table->__table_name;
        $joinSql = "left join " . $tableName. " " . $table->__alias_table_name . " on " . $onSql;
        if(empty($this->joinSql))
        {
            $this->joinSql = $joinSql;
        }
        else
        {
            $this->joinSql .= " " . $joinSql;
        }
        return $this;
    }

    public function rightJoin($table, AbstractWhereExpression $where)
    {
        if(empty($this->params))
        {
            $this->params = $where->params;
        }
        else if(! empty($where->params))
        {
            $this->params = array_merge($this->params, $where->params);
        }
        
        $onSql = $where->sql;
        $tableName = $table->__database . "." . $table->__table_name;
        $joinSql = "right join " . $tableName. " " . $table->__alias_table_name . " on " . $onSql;
        if(empty($this->joinSql))
        {
            $this->joinSql = $joinSql;
        }
        else
        {
            $this->joinSql .= " " . $joinSql;
        }
        return $this;
    }
    
    public function innerJoin($table, AbstractWhereExpression $where)
    {
        if(empty($this->params))
        {
            $this->params = $where->params;
        }
        else if(! empty($where->params))
        {
            $this->params = array_merge($this->params, $where->params);
        }
    
        $onSql = $where->sql;
        $tableName = $table->__database . "." . $table->__table_name;
        $joinSql = "inner join " . $tableName. " " . $table->__alias_table_name . " on " . $onSql;
        if(empty($this->joinSql))
        {
            $this->joinSql = $joinSql;
        }
        else
        {
            $this->joinSql .= " " . $joinSql;
        }
        return $this;
    }

    public function where(AbstractWhereExpression $where)
    {
        $this->whereSql = $where->sql;
        if(empty($this->params))
        {
            $this->params = $where->params;
        }
        else if(! empty($where->params))
        {
            $this->params = array_merge($this->params, $where->params);
        }
        return $this;
    }

    public function groupBy($colomnArgs)
    {
        $args = func_get_args();
        if(empty($args))
        {
            throw new \Exception("The group field is not inputed.");
        }
        
        for($i = 0; $i < count($args); $i ++)
        {
            $fieldStr = $args[$i];
            if(gettype($args[$i]) != "string")
            {
                $fieldStr = $args[$i]->aliasTableName . "." . $args[$i]->fieldName;
            }
            
            if($i == 0)
            {
                $this->groupBySql = $fieldStr;
            }
            else
            {
                $this->groupBySql .= "," . $fieldStr;
            }
        }
        return $this;
    }

    public function orderBy(Field $field, $order)
    {
        if(empty($order))
        {
            throw new \Exception("The order string is not inputed.");
        }
        $order = strtolower($order);
        if(! array_key_exists($order, MysqlDefine::$ORDERS))
        {
            throw new \Exception("The order string is not valid");
        }
        
        $order = MysqlDefine::$ORDERS[$order];
        $fieldName = $field->aliasTableName . "." . $field->fieldName;
        if(empty($this->orderBySql))
        {
            $this->orderBySql = $fieldName . " " . $order;
        }
        else
        {
            $this->orderBySql .= "," . $fieldName . " " . $order;
        }
        return $this;
    }

    public function limit($count)
    {
        $count = intval($count);
        if($count > 0)
        {
            $this->limitSql = "limit " . $count;
        }
        return $this;
    }

    public function getResult($classObject = null)
    {
        $sql = $this->getSql();
        
        return $this->getData($sql, $classObject);
    }

    public function getPageResult($pageIndex, $pageSize, $classObject = null)
    {
        $result=array(
        	"pageIndex"=>$pageIndex,
                "pageSize"=>$pageSize
        );
        
        $sql=$this->getSql();
        
        $sqlCount="select count(*) as num from (".$sql.") tmp";
        $countResult=$this->getData($sqlCount);
        $totalCount=$countResult[0]["num"];
        $totalPages=ceil($totalCount / $pageSize);
        $result["totalCount"]=$totalCount;
        $result["totalPages"]=$totalPages;
        
        if($pageIndex > $totalPages)
        {
            $result["data"]=array();
        }
        else 
        {
            $sql="select * from (".$sql.") tmp limit ".(($pageIndex - 1) * $pageSize) . ', ' . $pageSize;
            $dataResult=$this->getData($sql,$classObject);
            $result["data"]=$dataResult;
        }
        
        return $result;
    }

    private function getSql()
    {
        if(empty($this->fromSql))
        {
            throw new \Exception("The table seleted from is not inputed.");
        }
        
        $sql = "";
        if(empty($this->selectSql))
        {
            $this->selectSql = "*";
        }
        $sql .= "select " . $this->selectSql . " from " . $this->fromSql;
        if(! empty($this->joinSql))
        {
            $sql .= " " . $this->joinSql;
        }
        if(! empty($this->whereSql))
        {
            $sql .= " where " . $this->whereSql;
        }
        if(! empty($this->groupBySql))
        {
            $sql .= " group by " . $this->groupBySql;
        }
        if(! empty($this->orderBySql))
        {
            $sql .= " order by " . $this->orderBySql;
        }
        if(! empty($this->limitSql))
        {
            $sql .= " " . $this->limitSql;
        }
        $this->sql = $sql;
        return $sql;
    }

    private function getData($sql, $classObject=null)
    {
        $tempResult = $this->_pdoMysql->fetchAll($sql, $this->params);
        if(empty($classObject))
        {
        	return $tempResult;
        }
        $result = array();
        $parser = new DocParser($classObject);
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
}