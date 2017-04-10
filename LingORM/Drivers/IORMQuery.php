<?php
namespace LingORM\Drivers;

interface IORMQuery
{
	public function fetchOne($table,AbstractWhereExpression $where,AbstractOrderExpression $order=null);
	public function fetchAll($table,AbstractWhereExpression $where,AbstractOrderExpression $order=null,$top=0);
	public function insert($entity);
	public function batchInsert($entityArr, $nullIgnore=FALSE);
	public function update($entity, $nullIgnore=FALSE);
	public function batchUpdate($entityArr, $nullIgnore=FALSE);
	public function updateBy($table,$setParamArr,AbstractWhereExpression $where);
	public function delete($entity);
	public function deleteBy($table,AbstractWhereExpression $where);
	
	
}