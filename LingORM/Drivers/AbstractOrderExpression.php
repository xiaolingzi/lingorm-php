<?php
namespace LingORM\Drivers;

use LingORM\Mapping\Field;

abstract class AbstractOrderExpression
{
	public $sql;
	
	abstract public function orderBy(Field $field,$order);
}