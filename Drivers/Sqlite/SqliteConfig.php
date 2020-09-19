<?php
namespace LingORM\Drivers\Sqlite;

use LingORM\Drivers\DatabaseConfig;

class SqliteConfig
{
    const MODE_READ = "r";
    const MODE_WRITE = "w";

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
                if(empty($configMode) && $mode == self::MODE_READ){
                    array_push($targetDatabaseArr, $databaseInfo);
                }else if (strpos($configMode, $mode) !== false) {
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
}
