<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\INativeQuery;
use LingORM\Mapping\DocParser;

class MysqlNativeQuery implements INativeQuery
{
    private $_pdoMysql;
    private $_transactionKey;

    public function __construct($databaseInfo, $transactionKey = "")
    {
        $this->_transactionKey = $transactionKey;
        $this->_pdoMysql = new PDOMysql($databaseInfo, $this->_transactionKey);
    }

    public function excute($sql, $paramArr)
    {
        return $this->_pdoMysql->excute($sql, $paramArr);
    }

    public function insert($sql, $paramArr)
    {
        return $this->_pdoMysql->insert($sql, $paramArr);
    }

    public function find($sql, $paramArr, $classObject = null)
    {
        return $this->getData($sql, $paramArr, $classObject);
    }

    public function findPage($sql, $paramArr, $pageIndex, $pageSize, $classObject = null)
    {
        $result = array(
            "pageIndex" => $pageIndex,
            "pageSize" => $pageSize,
        );

        $sqlCount = "SELECT COUNT(*) as num from (" . $sql . ") tmp";
        $countResult = $this->getData($sqlCount, $paramArr);
        $totalCount = $countResult[0]["num"];
        $totalPages = ceil($totalCount / $pageSize);
        $result["totalCount"] = $totalCount;
        $result["totalPages"] = $totalPages;

        if ($pageIndex > $totalPages) {
            $result["data"] = array();
        } else {
            $sql = "SELECT * FROM (" . $sql . ") tmp LIMIT " . (($pageIndex - 1) * $pageSize) . ', ' . $pageSize;
            $dataResult = $this->getData($sql, $paramArr, $classObject);
            $result["data"] = $dataResult;
        }

        return $result;
    }

    public function findCount($sql, $paramArr)
    {
        $sqlCount = "SELECT COUNT(*) as num FROM (" . $sql . ") tmp";
        $countResult = $this->getData($sqlCount, $paramArr);
        $count = $countResult[0]["num"];
        return $count;
    }

    public function first($sql, $paramArr, $classObject = null)
    {
        $sql = "SELECT * FROM (" . $sql . ") tmp LIMIT 1";
        $list = $this->find($sql, $paramArr, $classObject);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }

    private function getData($sql, $paramArr, $classObject = null)
    {
        $tempResult = $this->_pdoMysql->fetchAll($sql, $paramArr);
        if (empty($classObject)) {
            return $tempResult;
        }
        $result = array();
        $parser = new DocParser($classObject);
        if (!empty($tempResult)) {
            for ($i = 0; $i < count($tempResult); $i++) {
                $entity = $parser->getObjectFromArray($tempResult[$i]);
                array_push($result, $entity);
            }
        }
        return $result;
    }
}
