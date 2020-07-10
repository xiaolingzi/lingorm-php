<?php
namespace LingORM\Drivers;

use LingORM\Mapping\Field;

abstract class AbstractOrderBy
{
    public $sql;

    abstract public function by(...$args);
}
