<?php
namespace LingORM\Drivers;

abstract class AbstractQueryBuilder
{
    public $sql;
    public $params;
    protected $selectSql;
    protected $fromSql;
    protected $joinSql;
    protected $whereSql;
    protected $groupBySql;
    protected $orderBySql;
    protected $limitSql;
    
	abstract public function select($colomnArgs);
	abstract public function from($table);
	abstract public function leftJoin($table,AbstractWhereExpression $where);
	abstract public function rightJoin($table,AbstractWhereExpression $where);
	abstract public function innerJoin($table,AbstractWhereExpression $where);
	abstract public function where(AbstractWhereExpression $where);
	abstract public function groupBy($colomnArgs);
	abstract public function orderBy($field,$order);
	abstract public function limit($count);
	abstract public function getResult($classObject=null);
	abstract public function getPageResult($pageIndex, $pageSize, $classObject = null);
	
}