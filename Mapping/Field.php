<?php

namespace LingORM\Mapping;

use LingORM\Common\ConstDefine;

class Field
{
    public $tableName;
    public $aliasTableName;
    public $fieldName;
    public $aliasFieldName;

    public $columnFuncs = array();
    public $isDistinct = 0;
    public $orderBy = 0;

    public function alias($aliasName)
    {
        $this->aliasFieldName = $aliasName;
        return $this;
    }

    public function count()
    {
        array_push($this->columnFuncs, "COUNT");
        return $this;
    }

    public function sum()
    {
        array_push($this->columnFuncs, "SUM");
        return $this;
    }

    public function max()
    {
        array_push($this->columnFuncs, "MAX");
        return $this;
    }

    public function min()
    {
        array_push($this->columnFuncs, "MIN");
        return $this;
    }

    public function f($func)
    {
        array_push($this->columnFuncs, $func);
        return $this;
    }

    public function distinct()
    {
        $this->isDistinct = 1;
        return $this;
    }

    //---where condition---//

    public function eq($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_EQUAL);
    }

    public function neq($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_NOT_EQUAL);
    }

    public function gt($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_GREATER_THAN);
    }

    public function ge($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_GREATER_EQUAL_THAN);
    }

    public function lt($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_LESS_THAN);
    }

    public function le($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_LESS_EQUAL_THAN);
    }

    public function in($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_IN);
    }

    public function nin($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_NOT_IN);
    }

    public function like($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_Like);
    }

    public function fis($value)
    {
        return $this->getCondition($value, ConstDefine::OPERATOR_FIND_IN_SET);
    }

    public function asc()
    {
        $this->orderBy = 0;
        return $this;
    }

    public function desc()
    {
        $this->orderBy = 1;
        return $this;
    }

    private function getCondition($value, $operatorType)
    {
        $result = new Condition();
        $result->aliasTableName = $this->aliasTableName;
        $result->fieldName = $this->fieldName;
        $result->operator = $operatorType;
        $result->value = $value;

        if ($value instanceof Field) {
            $result->valueType = 1;
        } else {
            $result->valueType = 0;
        }

        return $result;
    }
}
