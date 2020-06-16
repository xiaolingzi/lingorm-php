<?php
namespace LingORM\Drivers;

abstract class AbstractWhere
{
    public $sql;
    public $params = array();
    protected $index = 0;

    abstract public function and(...$args);
    abstract public function or(...$args);
    abstract public function getAnd(...$args);
    abstract public function getOr(...$args);
    abstract public function andOr(...$args);
    abstract public function orAnd(...$args);
}
