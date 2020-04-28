<?php
namespace LingORM\Drivers;

abstract class AbstractTableQuery
{
    public $sql;
    public $params;
    protected $selectSQL;
    protected $fromSQL;
    protected $whereSQL;
    protected $groupBySQL;
    protected $orderBySQL;
    protected $limitSQL;

    abstract public function select(...$args);
    abstract public function where(...$args);
    abstract public function groupBy(...$args);
    abstract public function orderBy(...$args);
    abstract public function limit($count);

    abstract public function first($classObject = null);
    abstract public function find($classObject = null);
    abstract public function findPage($pageIndex, $pageSize, $classObject = null);
    abstract public function findCount();

}