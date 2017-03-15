<?php
namespace LingORM\Drivers;

use LingORM\Config;
class DatabaseConfig
{
    private static $_configArr;
    
	public function getDatabaseConfig()
	{
	    if(empty(self::$_configArr))
	    {
	    	$filename=Config::getDatabaseConfigPath();
            self::$_configArr=$this->getArrayFromJsonFile($filename);
	    }
		return self::$_configArr;
	}
	
	public function getDatabaseInfoByDatabase($database)
	{
	   $configArr=$this->getDatabaseConfig();
	   foreach($configArr as $key=>$value)
	   {
	   	   if($value["database"]==$database)
	   	   {
	   	   	   return $value;
	   	   }
	   }
	   return array();
	}
	
	public function getDatabaseInfoByKey($key)
	{
	    $configArr=$this->getDatabaseConfig();
	    return $configArr[$key];
	}
	
	private function getArrayFromJsonFile($filename)
	{
	    $content=$this->getContentFromFile($filename);
	    if(!empty($content))
	    {
	        return json_decode($content,true);
	    }
	    return array();
	}
	
	private function getContentFromFile($filename)
	{
	    if(! file_exists($filename))
	    {
	        return null;
	    }
	    $content = file_get_contents($filename);
	    return $content;
	}
}