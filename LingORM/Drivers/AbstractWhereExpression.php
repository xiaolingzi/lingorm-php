<?php
namespace LingORM\Drivers;

abstract class AbstractWhereExpression
{
	public $sql;
	public $params=array();
	protected $index=0;
	
	abstract public function setAnd($expressionArgs);
	abstract public function setOr($expressionArgs);
	abstract public function getAnd($expressionArgs);
	abstract public function getOr($expressionArgs);
}