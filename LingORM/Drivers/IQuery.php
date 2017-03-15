<?php
namespace LingORM\Drivers;

interface IQuery
{
	public function getResult($sql, $paramArr,$classObject=null);
	public function getPageResult($sql, $paramArr,$pageIndex,$pageSize,$classObject=null);
	public function excute($sql, $paramArr);
}