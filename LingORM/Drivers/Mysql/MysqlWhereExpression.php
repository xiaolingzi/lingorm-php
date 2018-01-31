<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractWhereExpression;

class MysqlWhereExpression extends AbstractWhereExpression
{

    public function getAnd($expressionArgs)
    {
        $args = func_get_args();
        $sql = $this->getExpressionSql($args, 1);
        return $sql;
    }

    public function getOr($expressionArgs)
    {
        $args = func_get_args();
        $sql = $this->getExpressionSql($args, 2);
        return $sql;
    }

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
                $tempSql = $args[$i];
            }
            else if($args[$i] instanceof MysqlWhereExpression)
            {
                $tempSql = $args[$i]->sql;
            }
            else
            {
                $expression = MysqlDefine::getExpression($args[$i], $this->params);
                $this->params = $expression["params"];
                $tempSql = $expression["sql"];
            }
            
            $tempStr = preg_replace("#\\([^\\(\\)]*\\)#", "", $tempSql);
            if((strpos($tempStr, " or ")!==false && $type==1)
            || (strpos($tempStr, " and ")!==false && $type==2))
            {
                $tempSql = "(" . $tempSql . ")";
            }
            
            if($i == 0)
            {
                $sql = $tempSql;
            }
            else
            {
                if($type == 1)
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