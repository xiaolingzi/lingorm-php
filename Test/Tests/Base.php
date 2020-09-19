<?php
namespace LingORM\Test\Tests;

use LingORM\ORM;

class Base
{
    public function __construct()
    {
        putenv("LINGORM_CONFIG=" . dirname(dirname(__FILE__)) . "/config/database.json");
    }

    public function db(){
        return ORM::db("test");
    }
}
