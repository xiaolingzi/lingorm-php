<?php
namespace LingORM\Drivers;

abstract class AbstractQueryBuilder
{
    public $sql;
    public $params;
    protected $selectSQL;
    protected $fromSQL;
    protected $joinSQL;
    protected $whereSQL;
    protected $groupBySQL;
    protected $orderBySQL;
    protected $limitSQL;

    abstract public function select(...$args);
    abstract public function from($table);
    abstract public function leftJoin($table,  ...$whereArgs);
    abstract public function rightJoin($table,  ...$whereArgs);
    abstract public function innerJoin($table,  ...$whereArgs);
    abstract public function where( ...$whereArgs);
    abstract public function groupBy(...$args);
    abstract public function orderBy(...$args);
    abstract public function limit($count);

    abstract public function first($classObject = null);
    abstract public function find($classObject = null);
    abstract public function findPage($pageIndex, $pageSize, $classObject = null);
    abstract public function findCount();

}
