<?php
namespace LingORM;

class Config
{
	const DEFAULT_DATABASE_SERVER="test";
	
	static public function getDatabaseConfigPath()
	{
		return dirname(dirname(__DIR__)).'/config/'.ENVIRONMENT.'/database_config.json';
	}
}