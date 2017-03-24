<?php
namespace LingORM;

class Config
{
	const DEFAULT_DATABASE_SERVER="test";
	
	static public function getDatabaseConfigPath()
	{
		return dirname($_SERVER['SCRIPT_NAME']).'/config/database_config.json';
	}
}