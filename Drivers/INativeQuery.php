<?php
namespace LingORM\Drivers;

interface INativeQuery
{
    public function find($sql, $paramArr, $classObject = null);
    public function findPage($sql, $paramArr, $pageIndex, $pageSize, $classObject = null);
    public function findCount($sql, $paramArr);
    public function first($sql, $paramArr, $classObject = null);
    public function excute($sql, $paramArr);
}
