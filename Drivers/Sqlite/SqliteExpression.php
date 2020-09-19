<?php

namespace LingORM\Drivers\Sqlite;

use LingORM\Common\ConstDefine;

class SqliteExpression
{
    private static $index = 0;
    static $OPERATORS = array(
        "eq" => "=",
        "neq" => "<>",
        "gt" => ">",
        "ge" => ">=",
        "lt" => "<",
        "le" => "<=",
        "in" => "IN",
        "nin" => "NOT IN",
        "like" => "LIKE",
        'fis' => 'FIND_IN_SET',
    );

    public static function getExpression($condition, $params)
    {
        $sql = "";
        if ($condition->valueType == 1) {
            $fieldName = $condition->aliasTableName . "." . $condition->fieldName;
            $value = $condition->value->aliasTableName . "." . $condition->value->fieldName;
            $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " " . $value;
        } else {
            $fieldName = $condition->aliasTableName . "." . $condition->fieldName;
            if (is_null($condition->value)) {
                if ($condition->operator == ConstDefine::OPERATOR_EQUAL) {
                    $sql = $fieldName . " IS NULL";
                } else if ($condition->operator == ConstDefine::OPERATOR_NOT_EQUAL) {
                    $sql = $fieldName . " IS NOT NULL";
                } else {
                    throw new \Exception("Invalid parameter value for '$fieldName'!");
                }
            } else {
                $paramName = "p" . self::$index++;

                if ($condition->operator == ConstDefine::OPERATOR_IN or $condition->operator == ConstDefine::OPERATOR_NOT_IN) {
                    $inStr = "";
                    $tempValue = $condition->value;
                    if (is_string($tempValue)) {
                        $tempValue = explode(",", $tempValue);
                    }
                    if (is_array($tempValue)) {
                        $inIndex = 0;
                        foreach ($tempValue as $inVal) {
                            if (!is_null($inVal) && $inVal !== "") {
                                $tempName = $paramName . "_" . $inIndex;
                                $inStr .= ":" . $tempName . ",";
                                $params[$tempName] = $inVal;
                                $inIndex++;
                            }

                        }
                    }
                    $inStr = trim($inStr, ",");
                    $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " (" . $inStr . ")";
                } else if ($condition->operator == ConstDefine::OPERATOR_FIND_IN_SET) {
                    $params[$paramName] = $condition->value;
                    $sql = self::$OPERATORS[$condition->operator] . "(:" . $paramName . "," . $fieldName . ")";
                } else {
                    $params[$paramName] = $condition->value;
                    $sql = $fieldName . " " . self::$OPERATORS[$condition->operator] . " " . ":" . $paramName;

                }
            }
        }

        return array(
            "sql" => $sql,
            "params" => $params,
        );
    }
}
