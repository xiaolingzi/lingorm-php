<?php
$database = "";
$table = "";
$dir = "";

$inputParamArr = getopt("d:t:f:");

if(array_key_exists("d", $inputParamArr) && ! empty($inputParamArr["d"]))
{
    $database = $inputParamArr["d"];
}

if(array_key_exists("t", $inputParamArr) && !empty($inputParamArr["t"]))
{
    $table = $inputParamArr["t"];
}

if(array_key_exists("f", $inputParamArr) && !empty($inputParamArr["f"]))
{
    $dir = $inputParamArr["f"];
}

if(empty($database))
{
    $isInput = false;
    while(! $database)
    {
        if(! $isInput)
        {
            fwrite(STDOUT, 'Please input the database[d]:');
            $isInput = true;
        }
        else
        {
            fwrite(STDOUT, 'The database can not be empty, please input again[d]:');
        }
        
        $database = trim(fgets(STDIN));
    }
}

$tableArr = getTables($database);
if(empty($tableArr))
{
    echo "\n no tables.";
}

if(! empty($table))
{
    generateEntity($database, $table, $dir);
    echo "[".$table."] generated successfully!\n";
    exit;
}
else 
{
	foreach ($tableArr as $tempTable)
	{
	    generateEntity($database, $tempTable["Table_name"], $dir);
	    echo "[".$tempTable["Table_name"]."] generated successfully!\n";
	}
	exit;
}

function generateEntity($db, $tb,$dir)
{
    $entityName = getEntityName($tb);
    $entityContent = getTemplateContent();
    $entityContent = str_replace("{{table_name}}", $tb, $entityContent);
    $entityContent = str_replace("{{database_name}}", $db, $entityContent);
    $entityContent = str_replace("{{class_name}}", $entityName, $entityContent);
    
    $tempArr = array();
    $reg = "/\\<\\-\\-column\\-\\-\\>(.*)\\<\\-\\-column\\-\\-\\>/is";
    
    if(preg_match_all($reg, $entityContent, $tempArr) === false)
    {
        return $entityContent;
    }
    
    $cloumns = getCloumns($db, $tb);
    
    $propertTemplateContent = $tempArr[1][0];
    $propertyContent = "";
    if(! empty($cloumns))
    {
        foreach($cloumns as $column)
        {
            $columnProperty = 'name="' . $column["Column_name"] . '"';
            $dataType = getDataType($column["DATA_TYPE"]);
            $columnProperty .= ', type="' . $dataType . '"';
            if($dataType == "string" && ! empty($column["CHARACTER_MAXIMUM_LENGTH"]))
            {
                $columnProperty .= ', length="' . $column["CHARACTER_MAXIMUM_LENGTH"] . '"';
            }
            if($column["COLUMN_KEY"] == "PRI")
            {
                $columnProperty .= ', isId=1';
            }
            if($column["EXTRA"] == "auto_increment")
            {
                $columnProperty .= ', isGenerated=1';
            }
            $propertyName = getPropertyName($column["Column_name"]);
            $tempContent = str_replace("{{property_name}}", $propertyName, $propertTemplateContent);
            $tempContent = str_replace("{{column_property}}", $columnProperty, $tempContent);
            $propertyContent .= $tempContent . "\n";
        }
    }
    
    $entityContent = preg_replace($reg, $propertyContent, $entityContent);
    
    saveEntity($entityName, $entityContent, $dir);
}

function getTables($db)
{
    $sql = "select Table_name from TABLES where TABLE_SCHEMA='$db'";
    $result = fetchAll($sql, array());
    return $result;
}

function getCloumns($db, $tb)
{
    $sql = "select Column_name,DATA_TYPE, CHARACTER_MAXIMUM_LENGTH,COLUMN_KEY,EXTRA from COLUMNS where TABLE_SCHEMA='$db' and Table_name='$tb'";
    $result = fetchAll($sql, array());
    return $result;
}

function getDataType($type)
{
    $type = strtolower($type);
    $stringType = array(
            "char",
            "varchar",
            "nvarchar" 
    );
    $textType = array(
            "text",
            "longtext",
            "tinytext",
            "mediumtest" 
    );
    $intType = array(
            "int",
            "smallint",
            "tinyint",
            "bigint",
            "mediumint" 
    );
    $datetimeType = array(
            "datetime",
            "date",
            "time",
            "timestamp",
            "year" 
    );
    $doubleType = array(
            "double",
            "decimal" 
    );
    $floatType = array(
            "float" 
    );
    if(in_array($type, $stringType))
    {
        return "string";
    }
    else if(in_array($type, $textType))
    {
        return "text";
    }
    else if(in_array($type, $intType))
    {
        return "int";
    }
    else if(in_array($type, $datetimeType))
    {
        return "datetime";
    }
    else if(in_array($type, $doubleType))
    {
        return "double";
    }
    else if(in_array($type, $floatType))
    {
        return "float";
    }
    else
    {
        return "string";
    }
}

// --template--//
function getTemplateContent()
{
    $filename = __DIR__ . "/EntityTemplate.txt";
    return file_get_contents($filename);
}

function saveEntity($entityName, $content, $dir)
{
    if(empty($dir))
    {
        $dir = __DIR__ . "/entity";
    }
    if(! file_exists($dir))
    {
        mkdir($dir, 0777, true);
    }
    $filename = $dir . "/" . $entityName . ".php";
    $fp = fopen($filename, "w");
    fwrite($fp, $content);
    fclose($fp);
}

function getEntityName($tb)
{
    $reg = "/[_]+([^_]{1})/";
    $tb = preg_replace_callback($reg, function ($matches)
    {
        return strtoupper($matches[1]);
    }, $tb);
    $result = strtoupper(substr($tb, 0, 1)) . substr($tb, 1);
    return $result . "Entity";
}

function getPropertyName($columnName)
{
    $reg = "/[_]+([^_]{1})/";
    $columnName = preg_replace_callback($reg, function ($matches)
    {
        return strtoupper($matches[1]);
    }, $columnName);
    $result = strtolower(substr($columnName, 0, 1)) . substr($columnName, 1);
    return $result;
}

// --databse---//
function getConnection()
{
    $dbInfo = array(
            "host" => "127.0.0.1",
            "db" => "information_schema",
            "charset" => "utf8mb4",
            "user" => "xxx",
            "password" => "xxxxx" 
    );
    $dbConnection = new \PDO('mysql:host=' . $dbInfo["host"] . ';dbname=' . $dbInfo["db"] . ';charset=' . $dbInfo["charset"], $dbInfo["user"], $dbInfo["password"]);
    
    $dbConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    $dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
}

function prepareSql($sql, $paramArr)
{
    $dbConnection = getConnection();
    $statement = $dbConnection->prepare($sql);
    $statement->execute($paramArr);
    return $statement;
}

function fetchAll($sql, $paramArr)
{
    $statement = prepareSql($sql, $paramArr);
    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result;
}