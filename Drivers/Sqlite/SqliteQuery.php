<?php

namespace LingORM\Drivers\Sqlite;

use LingORM\Drivers\AbstractOrderBy;
use LingORM\Drivers\AbstractQuery;
use LingORM\Drivers\AbstractWhere;
use LingORM\Mapping\DocParser;

class SqliteQuery extends AbstractQuery
{
    private $_native;
    private $_transactionKey = "";

    public function __construct($databaseInfo)
    {
        $this->_databaseInfo = $databaseInfo;
        $this->_native = new SqliteNativeQuery($databaseInfo, $this->_transactionKey);
    }

    public function table($table)
    {
        return (new SqliteTableQuery($this->_databaseInfo, $this->_transactionKey))->table($table);
    }

    public function first($table, AbstractWhere $where, AbstractOrderBy $order = null)
    {
        $sql = $this->getSQL($table, $where, $order);

        $sql .= " LIMIT 1";

        $result = $this->_native->first($sql, $where->params, $table);
        return $result;
    }

    public function find($table, AbstractWhere $where, AbstractOrderBy $order = null)
    {
        $sql = $this->getSQL($table, $where, $order);

        $result = $this->_native->find($sql, $where->params, $table);
        return $result;
    }

    public function findPage($table, $pageIndex, $pageSize, AbstractWhere $where, AbstractOrderBy $order = null)
    {
        $sql = $this->getSQL($table, $where, $order);

        $result = $this->_native->findPage($sql, $where->params, $pageIndex, $pageSize, $table);
        return $result;
    }

    public function findTop($table, $limit, AbstractWhere $where, AbstractOrderBy $order = null)
    {
        $sql = $this->getSQL($table, $where, $order);

        $top = intval($limit);
        if ($top > 0) {
            $sql .= " LIMIT " . $top;
        }

        $result = $this->_native->find($sql, $where->params, $table);
        return $result;
    }

    public function findCount($table, AbstractWhere $where)
    {
        $sql = $this->getSQL($table, $where);

        $result = $this->_native->findCount($sql, $where->params);
        return $result;
    }

    private function getSQL($table, $where, $order = null)
    {
        if (empty($where)) {
            throw new \Exception("Missing the where condition!");
        }

        $tableName = $table->__table_name;
        if (!empty($table->__database)) {
            $tableName = $table->__database . "." . $tableName;
        }

        $sql = "SELECT * FROM " . $tableName . " " . $table->__alias_table_name . " WHERE " . $where->sql;
        if (!empty($order) && !empty($order->sql)) {
            $sql .= " ORDER BY " . $order->sql;
        }
        return $sql;
    }

    public function insert($entity)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("The entity class is not valid!");
        }

        $tableName = $table->name;
        if (!empty($table->database)) {
            $tableName = $table->database . "." . $tableName;
        }
        $fieldStr = "";
        $valueStr = "";
        $paramArr = array();
        $index = 0;
        foreach ($table->fieldArr as $field) {
            if ($field->isGenerated) {
                continue;
            }
            $tempFieldName = "f" . $index;
            $fieldStr .= $field->name . ",";
            $valueStr .= ":" . $tempFieldName . ",";
            $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            $index++;
        }
        $fieldStr = trim($fieldStr, ",");
        $valueStr = trim($valueStr, ",");

        $sql = "INSERT INTO $tableName ($fieldStr) VALUES ($valueStr)";

        return $this->_native->insert($sql, $paramArr);
    }

    public function batchInsert($entityArr, $nullIgnore = false)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if (!empty($table->database)) {
            $tableName = $table->database . "." . $tableName;
        }

        $fieldStr = "";
        $insertFieldArr = array();
        foreach ($table->fieldArr as $field) {
            if ($field->isGenerated) {
                continue;
            }
            if ($nullIgnore && is_null($field->value)) {
                continue;
            }
            array_push($insertFieldArr, $field->name);
            $fieldStr .= $field->name . ",";
        }
        $fieldStr = trim($fieldStr, ",");

        $valueStr = "";
        $paramArr = array();
        $index = 0;
        for ($i = 0; $i < count($entityArr); $i++) {
            $parser = new DocParser($entityArr[$i]);
            $table = $parser->getTable();
            $tempValueStr = "";

            foreach ($table->fieldArr as $field) {
                if ($field->isGenerated) {
                    continue;
                }
                if ($nullIgnore && !in_array($field->name, $insertFieldArr)) {
                    continue;
                }

                if (is_null($field->value)) {
                    $tempValueStr .= "DEFAULT,";
                } else {
                    $tempFieldName = "f" . $index;
                    $tempValueStr .= ":" . $tempFieldName . ",";
                    $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
                    $index++;
                }

            }
            $tempValueStr = trim($tempValueStr, ",");
            $valueStr .= "(" . $tempValueStr . "),";
        }
        $valueStr = trim($valueStr, ",");

        $sql = "INSERT INTO $tableName ($fieldStr) VALUES $valueStr";

        return $this->_native->excute($sql, $paramArr);
    }

    public function update($entity, $nullIgnore = false)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if (!empty($table->database)) {
            $tableName = $table->database . "." . $tableName;
        }

        $setStr = "";
        $whereStr = "";
        $paramArr = array();
        $index = 0;

        foreach ($table->fieldArr as $field) {
            if ($field->isPrimary) {
                $tempFieldName = "p" . $index;
                if (empty($whereStr)) {
                    $whereStr = $field->name . "=:" . $tempFieldName;
                } else {
                    $whereStr .= " AND " . $field->name . "=:" . $tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            }

            if ($field->isGenerated) {
                continue;
            }
            if ($nullIgnore && is_null($field->value)) {
                continue;
            }
            $tempFieldName = "f" . $index;
            $setStr .= $field->name . "=:" . $tempFieldName . ",";
            $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
            $index++;
        }
        $setStr = trim($setStr, ",");

        if (empty($whereStr)) {
            throw new \Exception("The 'update' method require at least one primary Key");
        }

        $sql = "UPDATE $tableName SET $setStr WHERE $whereStr";

        return $this->_native->excute($sql, $paramArr);
    }

    /**
     * only for one primary key table
     */
    public function batchUpdate($entityArr, $nullIgnore = false)
    {
        $parser = new DocParser($entityArr[0]);
        $table = $parser->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if (!empty($table->database)) {
            $tableName = $table->database . "." . $tableName;
        }

        $primaryCount = 0;
        $primaryPropertyName = "";
        $primaryColumnName = "";
        $primaryColumnType = "";
        foreach ($table->fieldArr as $key => $field) {
            if ($field->isPrimary) {
                $primaryColumnName = $field->name;
                $primaryPropertyName = $key;
                $primaryColumnType = $field->type;
                $primaryCount++;
            }
        }
        if ($primaryCount > 1) {
            throw new \Exception("This method applies only to tables that have only one primary key field");
        }

        $idStr = "";
        $paramArr = array();
        $setArr = array();
        $index = 0;
        for ($i = 0; $i < count($entityArr); $i++) {
            $parser = new DocParser($entityArr[$i]);
            $table = $parser->getTable();

            $primaryKeyName = "p" . $i;
            $paramArr[$primaryKeyName] = $this->getFieldValue($entityArr[$i]->{$primaryPropertyName}, $primaryColumnType);
            $idStr .= ":" . $primaryKeyName . ",";

            foreach ($table->fieldArr as $key => $field) {
                if ($field->isPrimary) {
                    continue;
                }
                if ($field->isGenerated) {
                    continue;
                }

                if ($nullIgnore && is_null($field->value)) {
                    continue;
                }

                $tempFieldName = "f" . $index;
                if (!array_key_exists($field->name, $setArr)) {
                    $setArr[$field->name] = " WHEN :" . $primaryKeyName . " THEN :" . $tempFieldName;
                } else {
                    $setArr[$field->name] .= " WHEN :" . $primaryKeyName . " THEN :" . $tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);

                $index++;
            }
        }
        $idStr = trim($idStr, ',');

        $setStr = "";
        foreach ($setArr as $key => $value) {
            $setStr .= "$key = CASE $primaryColumnName $value ELSE $key END,";
        }

        $setStr = trim($setStr, ',');

        $sql = "UPDATE $tableName SET $setStr WHERE $primaryColumnName IN($idStr)";

        return $this->_native->excute($sql, $paramArr);
    }

    /**
     * get the field value from the propert value of the entity
     *
     * @param unknown $originalValue
     * @param string $type
     * @return string unknown
     */
    private function getFieldValue($originalValue, $type)
    {
        if ($type == "datetime") {
            if (gettype($originalValue) == "object" && get_class($originalValue) == "DateTime") {
                return $originalValue->format("Y-m-d H:i:s");
            } else if (gettype($originalValue) == "integer") {
                return date("Y-m-d H:i:s", $originalValue);
            }
        }
        return $originalValue;
    }

    public function updateBy($table, $setParamArr, AbstractWhere $where)
    {
        if (empty($setParamArr)) {
            throw new \Exception("No field for update.");
        }
        if (empty($where)) {
            throw new \Exception("Missing the where condition!");
        }

        $setSql = "";
        for ($i = 0; $i < count($setParamArr); $i++) {
            $tempSql = "";
            if (gettype($setParamArr[$i]) == "string") {
                $tempSql = $setParamArr[$i];
            } else {
                $expression = SqliteExpression::getExpression($setParamArr[$i], $where->params);
                $where->params = $expression["params"];
                $tempSql = $expression["sql"];
            }

            if ($i == 0) {
                $setSql = $tempSql;
            } else {
                $setSql .= ", " . $tempSql;
            }
        }

        $tableName = $table->__table_name;
        if (!empty($table->__database)) {
            $tableName = $table->__database . "." . $tableName;
        }

        $sql = "UPDATE " . $tableName . " SET " . $setSql . " WHERE " . $where->sql;
        $sql = str_replace(" " . $table->__alias_table_name . ".", " ", $sql);
        $sql = str_replace("," . $table->__alias_table_name . ".", ",", $sql);

        $result = $this->_native->excute($sql, $where->params);
        return $result;
    }

    public function delete($entity)
    {
        $parser = new DocParser($entity);
        $table = $parser->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("The entity class is not valid!");
        }
        $tableName = $table->name;
        if (!empty($table->database)) {
            $tableName = $table->database . "." . $tableName;
        }

        $whereStr = "";
        $paramArr = array();
        $index = 0;

        foreach ($table->fieldArr as $field) {
            if ($field->isPrimary) {
                $tempFieldName = "p" . $index;
                if (empty($whereStr)) {
                    $whereStr = $field->name . "=:" . $tempFieldName;
                } else {
                    $whereStr .= " AND " . $field->name . "=:" . $tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);
                $index++;
            }
        }

        $sql = "DELETE FROM $tableName WHERE $whereStr";
        return $this->_native->excute($sql, $paramArr);
    }

    public function deleteBy($table, AbstractWhere $where)
    {
        if (empty($where)) {
            throw new \Exception("Missing the where condition!");
        }

        $tableName = $table->__table_name;
        if (!empty($table->__database)) {
            $tableName = $table->__database . "." . $tableName;
        }

        $sql = "DELETE FROM " . $tableName . " WHERE " . $where->sql;
        $sql = str_replace(" " . $table->__alias_table_name . ".", " ", $sql);
        $sql = str_replace("," . $table->__alias_table_name . ".", ",", $sql);

        $result = $this->_native->excute($sql, $where->params);
        return $result;
    }

    public function queryBuilder()
    {
        return new SqliteQueryBuilder($this->_databaseInfo, $this->_transactionKey);
    }
    public function nativeQuery()
    {
        return new SqliteNativeQuery($this->_databaseInfo, $this->_transactionKey);
    }
    public function createWhere()
    {
        return new SqliteWhere();
    }
    public function createOrderBy()
    {
        return new SqliteOrderBy();
    }

    public function begin()
    {
        $key = (new SqliteNative($this->_databaseInfo, $this->_transactionKey))->begin();
        $this->_transactionKey = $key;
        $this->_native = new SqliteNativeQuery($this->_databaseInfo, $this->_transactionKey);
    }
    public function commit()
    {
        (new SqliteNative($this->_databaseInfo, $this->_transactionKey))->commit();
    }
    public function rollback()
    {
        (new SqliteNative($this->_databaseInfo, $this->_transactionKey))->rollback();
    }
}
