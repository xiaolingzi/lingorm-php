<?php

namespace LingORM\Mapping;

use LingORM\Common\ConstDefine;
class Field
{
    public $tableName;
    public $aliasTableName;
    public $fieldName;
    public $aliasFieldName;
    
    public $isCount=0;
    public $isSum=0;
    public $isDistinct=0;

    public function alias($aliasName)
    {
        $this->aliasFieldName = $aliasName;
        return $this;
    }
    
    public function count()
    {
        $this->isCount = 1;
        return $this;
    }
    
    public function sum()
    {
        $this->isSum = 1;
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
    
    private function getCondition($value, $operatorType)
    {
        $result = new Condition();
        $result->aliasTableName = $this->aliasTableName;
        $result->fieldName = $this->fieldName;
        $result->operator=$operatorType;
        $result->value = $value;
        
        if(gettype($value)=="object" && get_class($value) == get_class($this))
        {
            $result->valueType = 1;
        }
        else
        {
            $result->valueType = 0;
        }
        
        return $result;
    }
}