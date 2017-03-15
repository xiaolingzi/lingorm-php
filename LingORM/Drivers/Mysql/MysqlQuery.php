<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\IQuery;
use LingORM\Mapping\DocParser;

class MysqlQuery implements IQuery
{
    private $_pdoMysql;
    
    public function __construct($databaseInfo)
    {
        $this->_pdoMysql = new PDOMysql($databaseInfo);
    }
    
    public function excute($sql, $paramArr)
    {
    	return $this->_pdoMysql->excute($sql, $paramArr);
    }

    public function getResult($sql, $paramArr, $classObject = null)
    {
        return $this->getData($sql, $paramArr, $classObject);
    }

    public function getPageResult($sql, $paramArr, $pageIndex, $pageSize, $classObject = null)
    {
        $result=array(
        	"pageIndex"=>$pageIndex,
                "pageSize"=>$pageSize
        );
        
        $sqlCount="select count(*) as num from (".$sql.") tmp";
        $countResult=$this->getData($sqlCount,$paramArr);
        $totalCount=$countResult[0]["num"];
        $totalPages=ceil($totalCount / $pageSize);
        $result["totalCount"]=$totalCount;
        $result["totalPages"]=$totalPages;
        
        if($pageIndex > $totalPages)
        {
            $result["data"]=array();
        }
        else 
        {
            $sql="select * from (".$sql.") tmp limit ".(($pageIndex - 1) * $pageSize) . ', ' . $pageSize;
            $dataResult=$this->getData($sql,$paramArr,$classObject);
            $result["data"]=$dataResult;
        }
        
        return $result;
    }

    private function getData($sql, $paramArr, $classObject=null)
    {
        $tempResult = $this->_pdoMysql->fetchAll($sql, $paramArr);
        if(empty($classObject))
        {
        	return $tempResult;
        }
        $result = array();
        $parser = new DocParser($classObject);
        if(! empty($tempResult))
        {
            for($i = 0; $i < count($tempResult); $i ++)
            {
                $entity = $parser->getObjectFromArray($tempResult[$i]);
                array_push($result, $entity);
            }
        }
        return $result;
    }
}