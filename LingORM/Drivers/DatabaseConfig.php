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
	       $tempInfo = $value;
	       if(array_key_exists("servers", $value) && !array_key_exists("database", $value))
	       {
	           $tempInfo = $value["servers"][0];
	       }
	   	   if($tempInfo["database"]==$database)
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
	
	public function getReadWriteDatabaseInfo($databaseInfo, $mode)
	{
	    if(!array_key_exists("servers", $databaseInfo))
	    {
	    	return $databaseInfo;
	    }
	    $databaseArr = $databaseInfo["servers"];
	    $readDatabaseArr = array();
	    $writeDatabaseArr = array();
	    foreach ($databaseArr as $databaseInfo)
	    {
	        if(array_key_exists("mode", $databaseInfo))
	        {
	            $configMode = strtolower($databaseInfo["mode"]);
	            $isWrite = strpos($configMode,"w")!==false;
	            $isRead = strpos($configMode,"r")!==false;
	            
	            if(!$isRead && !$isWrite)
	            {
	                $isRead = true;
	            }
	            
	            if($isRead)
	            {
	                array_push($readDatabaseArr, $databaseInfo);
	            }
	            
	            if($isWrite)
	            {
	                array_push($writeDatabaseArr, $databaseInfo);
	            }
	            
	        }
	        else 
	        {
	        	array_push($readDatabaseArr, $databaseInfo);
	        }
	    	
	    }
	    if(empty($readDatabaseArr) && empty($writeDatabaseArr))
	    {
	    	throw new \Exception("Database config error");
	    }
	    
	    $result = array();
	    $mode=strtolower($mode);
	    if($mode == "w")
	    {
	       if(empty($writeDatabaseArr))
	       {
	       	   throw new \Exception("No database for writing");
	       }
	       else 
	       {
	           $result = $this->getRandomDatabase($writeDatabaseArr, "w_weight");
	       }
	    }
	    else if($mode == "r")
	    {
	        if(empty($readDatabaseArr))
	        {
	            throw new \Exception("No database for reading");
	        }
	        else
	        {
	            $result = $this->getRandomDatabase($readDatabaseArr, "weight");
	        }
	    }
	    
	    
	    if(!array_key_exists("database", $result))
	    {
	    	$result["database"] = $databaseInfo["database"];
	    }
	    
	    if(!array_key_exists("user", $result))
	    {
	        $result["user"] = $databaseInfo["user"];
	    }
	    
	    if(!array_key_exists("password", $result))
	    {
	        $result["password"] = $databaseInfo["password"];
	    }
	    
	    if(!array_key_exists("charset", $result))
	    {
	        $result["charset"] = $databaseInfo["charset"];
	    }
	    
	    return $result;
	}
	
	private function getRandomDatabase($databaseArr, $weightKey)
	{
		$count = count($databaseArr);
		if($count==1)
		{
			return $databaseArr[0];
		}
		$sum = 0;
		for($i=0;$i<$count;$i++)
		{
			if(array_key_exists($weightKey, $databaseArr[$i]))
			{
			    $weight = intval($databaseArr[$i][$weightKey]);
			    if($weight < 0)
			    {
			    	$weight = 0;
			    }
			    $sum += $weight;
			}
			else 
			{
			    $sum += 1;
			}
			$databaseArr[$i]["sumweight"] = $sum;
		}
		
		$randomNumber = rand(1, $sum);
		$result = $databaseArr[0];
		
		for($i=0;$i<$count;$i++)
		{
		  if($randomNumber<=$databaseArr[$i]["sumweight"])
		  {
		      $result = $databaseArr[$i];
		      break;
		  }
		}
		
		return $result;
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