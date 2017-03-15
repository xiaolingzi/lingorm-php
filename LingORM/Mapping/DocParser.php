<?php
namespace LingORM\Mapping;

class DocParser
{
    private $_reflection;
    private $_classObject;
    
    public function __construct($classObject)
    {
        $className=get_class($classObject);
        $this->_reflection=new \ReflectionClass($className);
        $this->_classObject=$classObject;
    }
    
	public function getTable()
	{
		$tableDoc=$this->_reflection->getDocComment();
		if(empty($tableDoc))
		{
			return null;
		}
		$tempArr=array();
		$preg="/@Table[\\s]*\\(([^\\)]*)\\)/";
		if(preg_match_all($preg, $tableDoc,$tempArr)===false)
		{
			return null;
		}
		$paramArr=$this->getParameters($tempArr[1][0]);
		$result = new Table();
		if(!empty($paramArr))
		{
			if(array_key_exists("name", $paramArr))
			{
				$result->name=$paramArr["name"];
			}
			if(array_key_exists("database", $paramArr))
			{
			    $result->database=$paramArr["database"];
			}
		}
		if(empty($result->name))
		{
		    $className=$this->_reflection->getName();
		    $className=preg_replace("/[^\\\\]+\\\\/", "", $className);
			$result->name=$className;
		}
		
		$fieldArr=$this->getFields();
		$result->fieldArr=$fieldArr;
		
		return $result;
	}
	
	private function getFields()
	{
	    $propertyArr=$this->_reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
	    if(empty($propertyArr))
	    {
	    	return null;
	    }
	    $result=array();
	    foreach ($propertyArr as $property)
	    {
	        if(!$property->isDefault())
	        {
	            continue;
	        }
	    	$doc = $property->getDocComment();
	    	if(empty($doc))
	    	{
	    	    continue;
	    	}
	    	$tempArr=array();
	    	$preg="/@Column[\\s]*\\(([^\\)]*)\\)/";
	    	if(preg_match_all($preg, $doc,$tempArr)===false)
	    	{
	    	    continue;
	    	}
	    	if(empty($tempArr))
	    	{
	    		continue;
	    	}
	    	$paramArr=$this->getParameters($tempArr[1][0]);
	    	$column = new Column();
	    	if(!empty($paramArr))
	    	{
	    	    if(array_key_exists("name", $paramArr))
	    	    {
	    	        $column->name=$paramArr["name"];
	    	    }
	    	    if(array_key_exists("type", $paramArr))
	    	    {
	    	        $column->type=$paramArr["type"];
	    	    }
	    	    if(array_key_exists("length", $paramArr))
	    	    {
	    	        $column->length=$paramArr["length"];
	    	    }
	    	    if(array_key_exists("isGenerated", $paramArr))
	    	    {
	    	        $column->isGenerated=$paramArr["isGenerated"];
	    	    }
	    	    if(array_key_exists("isId", $paramArr))
	    	    {
	    	        $column->isId=$paramArr["isId"];
	    	    }
	    	}
	    	
	    	$value=$property->getValue($this->_classObject);
	    	$column->value=$value;
	    	
	    	$propertyName=$property->getName();
	    	if(empty($colum->name))
	    	{
	    	    $column->name=$propertyName;
	    	}
	    	
	    	$result[$propertyName]=$column;
	    	
	    }
	    return $result;
	}
	
	private function getParameters($paramStr)
	{
		if(empty($paramStr))
		{
			return null;
		}
		$result=array();
		$paramArr=explode(",", $paramStr);
		foreach ($paramArr as $tempStr)
		{
			if(empty($tempStr))
			{
				continue;
			}
			$tempStr=trim($tempStr," ");
			$tempArr=explode("=", $tempStr);
			if(count($tempArr)==2 && !empty($tempArr[0]))
			{
				$key=trim($tempArr[0]);
				$value=trim($tempArr[1],"\"' ");
				$result[$key]=$value;
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
	    
	    $fieldArr=array();
	    
	    if(property_exists($this->_classObject, "__fieldArr"))
	    {
	        $fieldArr=$this->_classObject->__fieldArr;
	    }
	    
	    if(empty($fieldArr))
	    {
	       $fieldArr=$this->getFields();
	    }
	    
	    foreach ($arr as $key=>$value)
	    {
	        $flag=false;
	        foreach ($fieldArr as $key1=>$value1)
	        {
	        	if($value1->name==$key)
	        	{
	        	    $result->{$key1}=FieldType::typeParse($value, $value1->type);
	        	    $flag=true;
	        	    break;
	        	}
	        }
	        if(!$flag)
	        {
                $result->{$key}=$value;
	        }
	    }
	    return $result;
	}
	
	
}