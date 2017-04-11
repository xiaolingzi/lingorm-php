<?php

namespace LingORM\Mapping;

class DocParser
{
    private $_reflection;
    private $_classObject;
    private $_nameMappingArr = array();

    public function __construct($classObject)
    {
        $className = get_class($classObject);
        $this->_reflection = new \ReflectionClass($className);
        $this->_classObject = $classObject;
    }

    public function getTable()
    {
        $tableDoc = $this->_reflection->getDocComment();
        if(empty($tableDoc))
        {
            return null;
        }
        $tempArr = array();
        $preg = "/@Table[\\s]*\\(([^\\)]*)\\)/";
        if(preg_match_all($preg, $tableDoc, $tempArr) === false)
        {
            return null;
        }
        $paramArr = $this->getParameters($tempArr[1][0]);
        $result = new Table();
        if(! empty($paramArr))
        {
            if(array_key_exists("name", $paramArr))
            {
                $result->name = $paramArr["name"];
            }
            if(array_key_exists("database", $paramArr))
            {
                $result->database = $paramArr["database"];
            }
        }
        if(empty($result->name))
        {
            $className = $this->_reflection->getName();
            $className = preg_replace("/[^\\\\]+\\\\/", "", $className);
            $result->name = $className;
        }
        
        $fieldArr = $this->getFields();
        $result->fieldArr = $fieldArr;
        
        return $result;
    }

    private function getFields()
    {
        $propertyArr = $this->_reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        if(empty($propertyArr))
        {
            return null;
        }
        $result = array();
        foreach($propertyArr as $property)
        {
            if(! $property->isDefault())
            {
                continue;
            }
            $doc = $property->getDocComment();
            if(empty($doc))
            {
                continue;
            }
            $tempArr = array();
            $preg = "/@Column[\\s]*\\(([^\\)]*)\\)/";
            if(preg_match_all($preg, $doc, $tempArr) === false)
            {
                continue;
            }
            if(empty($tempArr))
            {
                continue;
            }
            $propertyName = $property->getName();
            $paramArr = $this->getParameters($tempArr[1][0]);
            $column = new Column();
            if(! empty($paramArr))
            {
                if(array_key_exists("name", $paramArr))
                {
                    $column->name = $paramArr["name"];
                }
                else
                {
                    $column->name = $propertyName;
                }
                if(array_key_exists("type", $paramArr))
                {
                    $column->type = $paramArr["type"];
                }
                else
                {
                    $column->type = "string";
                }
                if(array_key_exists("length", $paramArr))
                {
                    $column->length = $paramArr["length"];
                }
                if(array_key_exists("isGenerated", $paramArr))
                {
                    $column->isGenerated = $paramArr["isGenerated"];
                }
                if(array_key_exists("primaryKey", $paramArr))
                {
                    $column->primaryKey = $paramArr["primaryKey"];
                }
            }
            
            $value = $property->getValue($this->_classObject);
            $column->value = $value;
            
            $result[$propertyName] = $column;
        }
        return $result;
    }

    private function getNameMapping()
    {
        $propertyArr = $this->_reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        if(empty($propertyArr))
        {
            return null;
        }
        $result = array();
        foreach($propertyArr as $property)
        {
            if(! $property->isDefault())
            {
                continue;
            }
            $doc = $property->getDocComment();
            if(empty($doc))
            {
                continue;
            }
            $tempArr = array();
            $preg = "/@Column[\\s]*\\(([^\\)]*)\\)/";
            if(preg_match_all($preg, $doc, $tempArr) === false)
            {
                continue;
            }
            if(empty($tempArr))
            {
                continue;
            }
            $propertyName = $property->getName();
            $paramArr = $this->getParameters($tempArr[1][0]);
            $column = new Column();
            if(! empty($paramArr))
            {
                if(array_key_exists("name", $paramArr))
                {
                    $column->name = $paramArr["name"];
                }
                else
                {
                    $column->name = $propertyName;
                }
                if(array_key_exists("type", $paramArr))
                {
                    $column->type = $paramArr["type"];
                }
                else
                {
                    $column->type = "string";
                }
                $result[$column->name] = array(
                        "type" => $column->type,
                        "propertyName" => $propertyName
                );
            }
        }
        if(empty($result))
        {
        	$result=array("1"=>1);
        }
        return $result;
    }

    private function getParameters($paramStr)
    {
        if(empty($paramStr))
        {
            return null;
        }
        $result = array();
        $paramArr = explode(",", $paramStr);
        foreach($paramArr as $tempStr)
        {
            if(empty($tempStr))
            {
                continue;
            }
            $tempStr = trim($tempStr, " ");
            $tempArr = explode("=", $tempStr);
            if(count($tempArr) == 2 && ! empty($tempArr[0]))
            {
                $key = trim($tempArr[0]);
                $value = trim($tempArr[1], "\"' ");
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getObjectFromArray($arr)
    {
        if(empty($arr))
        {
            return null;
        }
        $result = $this->_reflection->newInstance();
        
        if(empty($this->_nameMappingArr))
        {
            $this->_nameMappingArr = $this->getNameMapping();
        }
        
        foreach($arr as $key => $value)
        {
            if(array_key_exists($key, $this->_nameMappingArr))
            {
                $propertyName = $this->_nameMappingArr[$key]["propertyName"];
                $type = $this->_nameMappingArr[$key]["type"];
                $result->{$propertyName} = FieldType::typeParse($value, $type);
            }
            else
            {
                $result->{$key} = $value;
            }
        }
        return $result;
    }
}