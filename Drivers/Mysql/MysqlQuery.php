<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractOrderBy;
use LingORM\Drivers\AbstractQuery;
use LingORM\Drivers\AbstractWhere;
use LingORM\Mapping\DocParser;

class MysqlQuery extends AbstractQuery
{
    private $_native;
    private $_transactionKey = "";

    public function __construct($databaseInfo)
    {
        $this->_databaseInfo = $databaseInfo;
        $this->_native = new MysqlNativeQuery($databaseInfo, $this->_transactionKey);
    }

    public function table($table)
    {
        return (new MysqlTableQuery($this->_databaseInfo))->table($table);
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
        if (empty($where)) {
            throw new \Exception("Missing the where condition!");
        }

        $tableName = $table->__table_name;
        if (!empty($table->__database)) {
            $tableName = $table->__database . "." . $tableName;
        }

        $sql = "select * from " . $tableName . " " . $table->__alias_table_name . " where " . $where->sql;

        $result = $this->_native->findCount($sql, $where->params);
        return $result;
    }

    private function getSQL($table, $where, $order)
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
                    $tempValueStr .= "default,";
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
            if ($field->primaryKey) {
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

        $idCount = 0;
        $idPropertyName = "";
        $idFieldName = "";
        foreach ($table->fieldArr as $key => $field) {
            if ($field->primaryKey) {
                $idFieldName = $field->name;
                $idPropertyName = $key;
                $idCount++;
            }
        }
        if ($idCount > 1) {
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
            $paramArr[$primaryKeyName] = $entityArr[$i]->{$idPropertyName};
            $idStr .= ":" . $primaryKeyName . ",";

            foreach ($table->fieldArr as $key => $field) {
                if ($field->primaryKey) {
                    continue;
                }
                if ($field->isGenerated) {
                    continue;
                }

                if ($nullIgnore && is_null($field->value)) {
                    continue;
                }

                $tempIdName = "d" . $index;
                $paramArr[$tempIdName] = $entityArr[$i]->{$idPropertyName};

                $tempFieldName = "f" . $index;
                if (!array_key_exists($field->name, $setArr)) {
                    $setArr[$field->name] = " WHEN :" . $tempIdName . " THEN :" . $tempFieldName;
                } else {
                    $setArr[$field->name] .= " WHEN :" . $tempIdName . " THEN :" . $tempFieldName;
                }
                $paramArr[$tempFieldName] = $this->getFieldValue($field->value, $field->type);

                $index++;
            }
        }
        $idStr = trim($idStr, ',');

        $setStr = "";
        foreach ($setArr as $key => $value) {
            $setStr .= "$key = CASE $idFieldName $value ELSE $key END,";
        }

        $setStr = trim($setStr, ',');

        $sql = "UPDATE $tableName SET $setStr WHERE $idFieldName IN($idStr)";

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
                $expression = MysqlExpression::getExpression($setParamArr[$i], $where->params);
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

        $sql = "UPDATE " . $tableName . " " . $table->__alias_table_name . " SET " . $setSql . " WHERE " . $where->sql;

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
            if ($field->primaryKey) {
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

        $sql = "DELETE " . $table->__alias_table_name . " FROM " . $tableName . " " . $table->__alias_table_name . " WHERE " . $where->sql;

        $result = $this->_native->excute($sql, $where->params);
        return $result;
    }

    public function createQueryBuilder()
    {
        return new MysqlQueryBuilder($this->_databaseInfo);
    }
    public function createNative()
    {
        return new MysqlNativeQuery($this->_databaseInfo);
    }
    public function createWhere()
    {
        return new MysqlWhere();
    }
    public function createOrderBy()
    {
        return new MysqlOrderBy();
    }

    public function begin()
    {
        $key = (new PDOMysql($this->_databaseInfo, $this->_transactionKey))->begin();
        $this->_transactionKey = $key;
        $this->_native = new MysqlNativeQuery($this->_databaseInfo, $this->_transactionKey);
    }
    public function commit()
    {
        (new PDOMysql($this->_databaseInfo, $this->_transactionKey))->commit();
    }
    public function rollback()
    {
        (new PDOMysql($this->_databaseInfo, $this->_transactionKey))->rollback();
    }
}
