<?php

namespace LingORM\Drivers\Sqlite;

use LingORM\Drivers\AbstractTableQuery;

class SqliteTableQuery extends AbstractTableQuery
{
    private $_native;
    private $_table;
    public function __construct($databaseInfo, $transactionKey)
    {
        $this->_databaseInfo = $databaseInfo;
        $this->_native = new SqliteNativeQuery($databaseInfo, $transactionKey);
    }

    public function table($table)
    {
        $this->_table = $table;
        $sql = $table->__table_name . " " . $table->__alias_table_name;
        if (!empty($table->__database)) {
            $sql = $table->__database . "." . $sql;
        }
        $this->fromSQL = $sql;
        return $this;
    }

    public function select(...$args)
    {
        $this->selectSQL = (new SqliteColumn())->getSelectColumns($args);
        return $this;
    }

    public function where(...$args)
    {
        $where = new SqliteWhere();
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
        $order = new SqliteOrderBy();
        $order->by(...$args);
        if (empty($this->orderBySQL)){
            $this->orderBySQL = $order->sql;
        }else{
            $this->orderBySQL .= "," . $order->sql;
        }
        
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

    public function first()
    {
        $sql = $this->getSQL();
        $result = $this->_native->first($sql, $this->params, $this->_table);
        return $result;
    }

    public function find()
    {
        $sql = $this->getSQL();
        $result = $this->_native->find($sql, $this->params, $this->_table);
        return $result;
    }

    public function findPage($pageIndex, $pageSize)
    {
        $sql = $this->getSQL();
        $result = $this->_native->findPage($sql, $this->params, $pageIndex, $pageSize, $this->_table);
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
        return $sql;
    }
}
