<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Common\ConstDefine;

class MysqlDefine
{
    private static $index = 0;
    static $OPERATORS = array(
            "eq" => "=",
            "neq" => "<>",
            "gt" => ">",
            "ge" => ">=",
            "lt" => "<",
            "le" => "<=",
            "in" => "in",
            "nin" => "not in",
            "like" => "like" 
    );
    static $ORDERS = array(
            "asc" => "asc",
            "desc" => "desc" 
    );

    static public function getExpression($condition, $params)
    {
        $sql = "";
        if($condition->valueType == 1)
        {
            $fieldName = $condition->aliasTableName . "." . $condition->fieldName;
            $value = $condition->value->aliasTableName . "." . $condition->value->fieldName;
            $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " " . $value;
        }
        else
        {
            $fieldName = $condition->aliasTableName . "." . $condition->fieldName;
            if(is_null($condition->value))
            {
                if($condition->operator == ConstDefine::OPERATOR_EQUAL)
                {
                	$sql = $fieldName." is null";
                }
                else if($condition->operator == ConstDefine::OPERATOR_NOT_EQUAL)
                {
                    $sql = $fieldName." is not null";
                }
                else 
                {
                	throw new \Exception("Invalid parameter value for '$fieldName'!");
                }
            }
            else 
            {
                $paramName = "p" . self::$index ++;
                $params[$paramName] = $condition->value;
                
                if($condition->operator == ConstDefine::OPERATOR_IN or $condition->operator == ConstDefine::OPERATOR_NOT_IN)
                {
                    $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " (" . ":" . $paramName . ")";
                }
                else
                {
                    $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " " . ":" . $paramName;
                    
                }
            }
        }
        
        return array(
                "sql" => $sql,
                "params" => $params 
        );
    }
}