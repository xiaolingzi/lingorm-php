<?php
namespace LingORM\Drivers;

class DatabaseConfig
{
    public static $configFile = "";
    private static $_configArr;
    const MODE_READ = "r";
    const MODE_WRITE = "w";

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

    public function getDatabaseInfoByDatabase($database)
    {
        $configArr = $this->getDatabaseConfig();
        foreach ($configArr as $key => $value) {
            $tempInfo = $value;
            if (array_key_exists("servers", $value) && !array_key_exists("database", $value)) {
                $tempInfo = $value["servers"][0];
            }
            if ($tempInfo["database"] == $database) {
                return $value;
            }
        }
        return array();
    }

    public function getDatabaseInfoByKey($key)
    {
        $configArr = $this->getDatabaseConfig();
        return $configArr[$key];
    }

    public function getReadWriteDatabaseInfo($databaseInfo, $mode)
    {
        if (!array_key_exists("servers", $databaseInfo)) {
            return $databaseInfo;
        }
        $databaseArr = $databaseInfo["servers"];
        if (empty($databaseArr)) {
            return $databaseInfo;
        }

        $targetDatabaseArr = array();

        foreach ($databaseArr as $databaseInfo) {
            if ($mode == self::MODE_READ) {
                $databaseInfo["weight"] = 0;
                if (array_key_exists("rweight", $databaseInfo)) {
                    $databaseInfo["weight"] = intval($databaseInfo["rweight"]);
                }
            } else if ($mode == self::MODE_WRITE) {
                $databaseInfo["weight"] = 0;
                if (array_key_exists("wweight", $databaseInfo)) {
                    $databaseInfo["weight"] = intval($databaseInfo["wweight"]);
                }
            }
            if (!array_key_exists("weight", $databaseInfo) && $databaseInfo["weight"] <= 0) {
                continue;
            }

            if (array_key_exists("mode", $databaseInfo)) {
                $configMode = strtolower($databaseInfo["mode"]);
                if (strpos($configMode, $mode) !== false) {
                    array_push($targetDatabaseArr, $databaseInfo);
                } else if ($mode == self::MODE_READ) {
                    array_push($targetDatabaseArr, $databaseInfo);
                }
            } else if ($mode == self::MODE_READ) {
                array_push($targetDatabaseArr, $databaseInfo);
            }
        }

        if (empty($targetDatabaseArr)) {
            throw new \Exception("Database config error");
        }

        $result = $this->getRandomDatabase($targetDatabaseArr);

        if (!array_key_exists("database", $result)) {
            $result["database"] = $databaseInfo["database"];
        }

        if (!array_key_exists("user", $result)) {
            $result["user"] = $databaseInfo["user"];
        }

        if (!array_key_exists("password", $result)) {
            $result["password"] = $databaseInfo["password"];
        }

        if (!array_key_exists("charset", $result)) {
            $result["charset"] = $databaseInfo["charset"];
        }

        return $result;
    }

    private function getRandomDatabase($databaseArr)
    {
        $count = count($databaseArr);
        if ($count == 1) {
            return $databaseArr[0];
        }
        $sum = 0;
        for ($i = 0; $i < $count; $i++) {
            $weight = intval($databaseArr[$i]["weight"]);
            $sum += $weight;
            $databaseArr[$i]["weight"] = $sum;
        }

        $randomNumber = rand(1, $sum);
        $result = $databaseArr[0];

        for ($i = 0; $i < $count; $i++) {
            if ($randomNumber <= $databaseArr[$i]["weight"]) {
                $result = $databaseArr[$i];
                break;
            }
        }

        return $result;
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
