<?php
namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractOrderBy;
use LingORM\Mapping\Field;

class MysqlOrderBy extends AbstractOrderBy
{
    private $_orderArr = array("ASC", "DESC");
    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";

    public function orderBy(...$args)
    {
        $order = "";
        foreach ($args as $arg) {
            if ($arg instanceof Field) {
                $this->sql .= "," . $arg->aliasTableName . "." . $arg->fieldName . " " . $this->_orderArr[$arg->orderBy];

            } else if (gettype($arg) == "string") {
                $order .= "," . $arg;
            }
        }
        $this->sql = trim($this->sql, ",");
        return $this;
    }

}
