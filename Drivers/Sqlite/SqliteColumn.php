<?php

namespace LingORM\Drivers\Sqlite;

use LingORM\Mapping\Field;

class SqliteColumn
{
    public function getSelectColumns($cloumnsArr)
    {
        $selectSql = "";
        if (empty($cloumnsArr)) {
            return "*";
        }

        for ($i = 0; $i < count($cloumnsArr); $i++) {
            $column = $cloumnsArr[$i];
            if (empty($column)) {
                continue;
            }

            $fieldStr = $column;

            if ($column instanceof Field) {
                $fieldStr = $column->aliasTableName . "." . $column->fieldName;

                if ($column->isDistinct == 1) {
                    $fieldStr = "DISTINCT " . $fieldStr;
                }
                if (!empty($column->columnFuncs)) {
                    foreach ($column->columnFuncs as $columnFunc) {
                        $fieldStr = $columnFunc . "(" . $fieldStr . ")";
                    }
                }
                if (!empty($column->aliasFieldName)) {
                    $fieldStr = $fieldStr . " AS " . $column->aliasFieldName;
                }
            } else if (gettype($column) == "object" && property_exists($column, "__alias_table_name")) {
                $fieldStr = $column->__alias_table_name . ".*";
            }

            if ($i == 0) {
                $selectSql = $fieldStr;
            } else {
                $selectSql .= "," . $fieldStr;
            }
        }
        return $selectSql;
    }
}
