<?php
namespace LingORM\Test\Mysql;

use LingORM\ORM;

class Base
{
    public function __construct()
    {
        putenv("LINGORM_CONFIG=" . dirname(dirname(__FILE__)) . "/config/database_config.json");
    }

    public function db(){
        return ORM::db("test");
    }
}
