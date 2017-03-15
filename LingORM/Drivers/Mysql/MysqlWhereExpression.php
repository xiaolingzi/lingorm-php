<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractWhereExpression;

class MysqlWhereExpression extends AbstractWhereExpression
{

    public function setAnd($expressionArgs)
    {
        $args = func_get_args();
        $this->sql = $this->getExpressionSql($args, 1);
        return $this->sql;
    }

    public function setOr($expressionArgs)
    {
        $args = func_get_args();
        $this->sql = $this->getExpressionSql($args, 2);
        return $this->sql;
    }

    private function getExpressionSql($args, $type)
    {
        if(empty($args))
        {
            return "";
        }
        $sql = "";
        for($i = 0; $i < count($args); $i ++)
        {
            $tempSql = "";
            if(gettype($args[$i]) == "string")
            {
                $tempSql = "(" . $args[$i] . ")";
            }
            else
            {
                $expression = MysqlDefine::getExpression($args[$i], $this->params);
                $this->params = $expression["params"];
                $tempSql = $expression["sql"];
            }
            if($i == 0)
            {
                $sql = $tempSql;
            }
            else
            {
                if($type==1)
                {
                    $sql .= " and " . $tempSql;
                }
                else 
                {
                    $sql .= " or " . $tempSql;
                }
            }
        }
        return $sql;
    }
    
    
}