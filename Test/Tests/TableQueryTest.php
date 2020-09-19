<?php
namespace LingORM\Test\Tests;

use LingORM\Test\Entity\FirstTableEntity;
use PHPUnit\Framework\TestCase;

require_once "Base.php";

class TableQueryTest extends TestCase
{
    private static $db;
    public static function setUpBeforeClass(): void
    {
        self::$db = (new Base())->db();
        self::$db->begin();
        $entityArr = array();
        for ($i = 1; $i < 3; $i++) {
            $entity = new FirstTableEntity();
            $entity->id = $i;
            $entity->firstName = "table query " . $i;
            $entity->firstNumber = 2002;
            $entity->firstTime = "2019-09-02";
            array_push($entityArr, $entity);
        }
        self::$db->batchInsert($entityArr);
    }

    public static function tearDownAfterClass(): void
    {
        self::$db->rollback();
        self::$db = null;
    }

    public function testFirst()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $entity = self::$db->table($table)->select($table->firstName, $table->firstNumber)->where($table->firstName->like("table query%"))->first();
        $this->assertNotNull($entity);
        $this->assertEquals(2002, $entity->firstNumber);
    }

    public function testFind()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $list = self::$db->table($table)->where($table->firstName->like("table query%"))->find();
        $this->assertNotNull($list);
        $this->assertEquals(2, count($list));
        $this->assertEquals(2002, $list[0]->firstNumber);
    }

    public function testGroupBy()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $list = self::$db->table($table)
            ->select($table->firstNumber, $table->firstName->max()->alias("firstName"), $table->id->count()->alias("num"))
            ->where($table->firstName->like("table query%"))->groupBy($table->firstNumber)->find();
        $this->assertNotNull($list);
        $this->assertEquals(1, count($list));
        $this->assertEquals("table query 2", $list[0]->firstName);
        $this->assertEquals(2, $list[0]->num);
    }

    public function testFindPage()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $result = self::$db->table($table)->where($table->firstName->like("table query%"))->findPage(1, 1);
        $this->assertNotNull($result);
        $this->assertEquals(2, $result["totalPages"]);
        $this->assertEquals(2002, $result["data"][0]->firstNumber);
    }

    public function testFindCount()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $result = self::$db->table($table)->where($table->firstName->like("table query%"))->findCount();
        $this->assertEquals(2, $result);
    }

}
