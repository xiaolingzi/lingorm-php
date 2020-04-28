<?php

namespace LingORM\Drivers\Mysql;

use LingORM\Drivers\AbstractTableQuery;

class MysqlTableQuery extends AbstractTableQuery
{
    private $_native;
    public function __construct($databaseInfo)
    {
        $this->_databaseInfo = $databaseInfo;
        $this->_native = new MysqlNativeQuery($databaseInfo);
    }

    public function table($table)
    {
        $sql = $table->__table_name . " " . $table->__alias_table_name;
        if (!empty($table->__database)) {
            $sql = $table->__database . "." . $sql;
        }
        $this->fromSQL = $sql;
        return $this;
    }

    public function select(...$args)
    {
        $this->selectSQL = (new MysqlColumn())->getSelectColumns($args);
        return $this;
    }

    public function where(...$args)
    {
        $where = new MysqlWhere();
        $where->and(...$args);
        $this->whereSQL = $where->sql;
        if (empty($this->params)) {
            $this->params = $where->params;
        } else if (!empty($where->params)) {
            $this->params = array_merge($this->params, $where->params);
        }

        return $this;
    }

    public function groupBy(...$args)
    {
        if (empty($args)) {
            throw new \Exception("The group field is not inputed.");
        }

        for ($i = 0; $i < count($args); $i++) {
            $fieldStr = $args[$i];
            if (gettype($args[$i]) != "string") {
                $fieldStr = $args[$i]->aliasTableName . "." . $args[$i]->fieldName;
            }

            if ($i == 0) {
                $this->groupBySQL = $fieldStr;
            } else {
                $this->groupBySQL .= "," . $fieldStr;
            }
        }
        return $this;
    }

    public function orderBy(...$args)
    {
        $order = new MysqlOrderBy();
        $order->orderBy(...$args);
        $this->orderBySQL = $order->sql;
        return $this;
    }

    public function limit($count)
    {
        $count = intval($count);
        if ($count > 0) {
            $this->limitSQL = "LIMIT " . $count;
        }
        return $this;
    }

    public function first($classObject = null)
    {
        $sql = $this->getSQL();
        $result = $this->_native->first($sql, $this->params, $classObject);
        return $result;
    }

    public function find($classObject = null)
    {
        $sql = $this->getSQL();
        $result = $this->_native->find($sql, $this->params, $classObject);
        return $result;
    }

    public function findPage($pageIndex, $pageSize, $classObject = null)
    {
        $sql = $this->getSQL();
        $result = $this->_native->find($sql, $this->params, $classObject);
        return $result;
    }

    public function findCount()
    {
        $sql = $this->getSQL();
        $result = $this->_native->findCount($sql, $this->params);
        return $result;
    }

    public function getSQL()
    {
        if (empty($this->fromSQL)) {
            throw new \Exception("The table seleted from is not inputed.");
        }

        $sql = "";
        if (empty($this->selectSQL)) {
            $this->selectSQL = "*";
        }
        $sql .= "SELECT " . $this->selectSQL . " FROM " . $this->fromSQL;
        if (!empty($this->whereSQL)) {
            $sql .= " WHERE " . $this->whereSQL;
        }
        if (!empty($this->groupBySQL)) {
            $sql .= " GROUP BY " . $this->groupBySQL;
        }
        if (!empty($this->orderBySQL)) {
            $sql .= " ORDER BY " . $this->orderBySQL;
        }
        if (!empty($this->limitSQL)) {
            $sql .= " " . $this->limitSQL;
        }
        $this->sql = $sql;
        var_dump($sql);
        return $sql;
    }
}
