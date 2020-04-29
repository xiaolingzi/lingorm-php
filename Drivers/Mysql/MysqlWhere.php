<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractWhere;

class MysqlWhere extends AbstractWhere
{

    public function getAnd(...$args)
    {
        $sql = $this->getExpressionSql($args, 1);
        return $sql;
    }

    public function getOr(...$args)
    {
        $args = func_get_args();
        $sql = $this->getExpressionSql($args, 2);
        return $sql;
    }

    function  and (...$args) {
        array_unshift($args, $this);
        $this->sql = $this->getExpressionSql($args, 1);
        return $this;
    }

    function  or (...$args) {
        array_unshift($args, $this);
        $this->sql = $this->getExpressionSql($args, 2);
        return $this;
    }

    private function getExpressionSql($args, $type)
    {
        if (empty($args)) {
            return "";
        }

        $sql = "";
        for ($i = 0; $i < count($args); $i++) {
            $tempSql = "";
            if (gettype($args[$i]) == "string") {
                $tempSql = $args[$i];
            } else if ($args[$i] instanceof MysqlWhere) {
                $tempSql = $args[$i]->sql;
                $this->params = $args[$i]->params;
            } else {
                $expression = MysqlExpression::getExpression($args[$i], $this->params);
                $this->params = $expression["params"];
                $tempSql = $expression["sql"];
            }

            if (empty($tempSql)) {
                continue;
            }

            $tempStr = preg_replace("#\\([^\\(\\)]*\\)#", "", $tempSql);
            $tempStr = strtoupper($tempStr);
            if ((strpos($tempStr, " OR ") !== false && $type == 1)
                || (strpos($tempStr, " AND ") !== false && $type == 2)) {
                $tempSql = "(" . $tempSql . ")";
            }

            if (empty($sql)) {
                $sql = $tempSql;
            } else {
                if ($type == 1) {
                    $sql .= " AND " . $tempSql;
                } else {
                    $sql .= " OR " . $tempSql;
                }
            }
        }
        return $sql;
    }
}
