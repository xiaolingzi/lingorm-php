<?php
namespace LingORM\Drivers\Mysql;


use LingORM\Drivers\AbstractOrderExpression;
use LingORM\Mapping\Field;

class MysqlOrderExpression extends AbstractOrderExpression
{
    const ORDER_ASC="asc";
    const ORDER_DESC="desc";
    
    public function orderBy(Field $field, $order)
    {
        if(empty($order))
        {
        	throw new \Exception("The order string is not inputed.");
        }
        $order=strtolower($order);
        if(!array_key_exists($order, MysqlDefine::$ORDERS))
        {
            throw new \Exception("The order string is not valid");
        }
        
        $order=MysqlDefine::$ORDERS[$order];
        $fieldName=$field->aliasTableName.".".$field->fieldName;
        if(empty($this->sql))
        {
            $this->sql=$fieldName." ".$order;
        }
        else 
        {
            $this->sql.=",".$fieldName." ".$order;
        }
        return $this;
    }


    
    

    
}