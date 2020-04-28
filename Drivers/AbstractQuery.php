<?php
namespace LingORM\Drivers;

use LingORM\Mapping\DocParser;
use LingORM\Mapping\Field;

abstract class AbstractQuery
{
    protected $_databaseInfo;
    private static $_index = 0;
    private static $_tableArr = array();

    abstract public function table($table);
    abstract public function first($table, AbstractWhere $where, AbstractOrderBy $order = null);
    abstract public function find($table, AbstractWhere $where, AbstractOrderBy $order = null);
    abstract public function findTop($table, $limit, AbstractWhere $where, AbstractOrderBy $order = null);
    abstract public function findPage($table, $pageIndex, $pageSize, AbstractWhere $where, AbstractOrderBy $order = null);
    abstract public function findCount($table, AbstractWhere $where);

    abstract public function insert($entity);
    abstract public function batchInsert($entityArr, $nullIgnore = false);
    abstract public function update($entity, $nullIgnore = false);
    abstract public function batchUpdate($entityArr, $nullIgnore = false);
    abstract public function updateBy($table, $setParamArr, AbstractWhere $where);
    abstract public function delete($entity);
    abstract public function deleteBy($table, AbstractWhere $where);

    abstract public function createQueryBuilder();
    abstract public function createNative();
    abstract public function createWhere();
    abstract public function createOrderBy();

    abstract public function begin();
    abstract public function commit();
    abstract public function rollback();

    /**
     * @param class $classInstance
     * @param string $aliasTableName
     * @param string $database
     * @throws \Exception
     * @return class object
     */
    public function createTable($entity, $aliasTableName = null)
    {
        $className = get_class($entity);
        if (empty($aliasTableName) && array_key_exists($className, self::$_tableArr)) {
            $tempObj = serialize(self::$_tableArr[$className]);
            $result = unserialize($tempObj);
            return $result;
        }

        $table = (new DocParser($entity))->getTable();
        if (empty($table) || empty($table->fieldArr)) {
            throw new \Exception("Model invalid!");
        }

        if (empty($this->_databaseInfo)) {
            throw new \Exception("Missing database configuration!");
        }

        if (empty($table->database)) {
            $table->database = $this->_databaseInfo["database"];
        }

        $tableName = $table->name;
        $entity->{"__table_name"} = $tableName;

        if (empty($aliasTableName)) {
            self::$_index = self::$_index + 1;
            $aliasTableName = "t" . self::$_index;
        }
        $entity->{"__alias_table_name"} = $aliasTableName;

        $entity->{"__database"} = $table->database;

        $entity->{"__fieldArr"} = $table->fieldArr;

        foreach ($table->fieldArr as $key => $value) {
            $field = new Field();
            $field->tableName = $tableName;
            $field->aliasTableName = $aliasTableName;
            $field->fieldName = $value->name;
            $field->aliasFieldName = null;
            $entity->{$key} = $field;
        }

        self::$_tableArr[$className] = $entity;

        $tempObj = serialize(self::$_tableArr[$className]);
        $result = unserialize($tempObj);
        return $result;
    }
}
