<?php
namespace LingORM\Drivers\Sqlite;

use LingORM\Drivers\AbstractOrderBy;
use LingORM\Mapping\Field;

class SqliteOrderBy extends AbstractOrderBy
{
    private $_orderArr = array("ASC", "DESC");
    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";

    public function by(...$args)
    {
        $order = "";
        foreach ($args as $arg) {
            if ($arg instanceof Field) {
                $order .= "," . $arg->aliasTableName . "." . $arg->fieldName . " " . $this->_orderArr[$arg->orderBy];

            } else if (gettype($arg) == "string") {
                $order .= "," . $arg;
            }
        }
        $order = trim($order, ",");
        if (empty($this->sql)){
            $this->sql = $order;
        }else{
            $this->sql .= "," . $order;
        }
        
        return $this;
    }

}
