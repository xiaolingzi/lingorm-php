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

    abstract public function first();
    abstract public function find();
    abstract public function findPage($pageIndex, $pageSize);
    abstract public function findCount();

}