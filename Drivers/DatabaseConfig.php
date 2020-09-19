<?php
namespace LingORM\Drivers;

class DatabaseConfig
{
    public static $configFile = "";
    private static $_configArr;

    public function getDatabaseInfoByKey($key)
    {
        $configArr = $this->getDatabaseConfig();
        return $configArr[$key];
    }

    public function getDatabaseConfig()
    {
        if (empty(self::$_configArr)) {
            if (empty(self::$configFile) && getenv("LINGORM_CONFIG") !== false) {
                self::$configFile = getenv("LINGORM_CONFIG");
            }
            if (empty(self::$configFile)) {
                throw new \Exception("Database config file not found.");
            }
            $filename = self::$configFile;
            self::$_configArr = $this->getArrayFromJsonFile($filename);
        }
        return self::$_configArr;
    }

    private function getArrayFromJsonFile($filename)
    {
        $content = $this->getContentFromFile($filename);
        if (!empty($content)) {
            return json_decode($content, true);
        }
        return array();
    }

    private function getContentFromFile($filename)
    {
        if (!file_exists($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        return $content;
    }
}
