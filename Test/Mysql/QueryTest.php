<?php
namespace LingORM\Test\Mysql;

use LingORM\Test\Entity\FirstTableEntity;
use PHPUnit\Framework\TestCase;

require_once "Base.php";

class QueryTest extends TestCase
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
            $entity->firstName = "query " . $i;
            $entity->firstNumber = 3002;
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
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("query%"));
        $orderBy = self::$db->createOrderBy();
        $orderBy->by($table->id->desc());

        $entity = self::$db->first($table, $where);
        $this->assertNotNull($entity);
        $this->assertEquals("query 1", $entity->firstName);

        $entity = self::$db->first($table, $where, $orderBy);
        $this->assertNotNull($entity);
        $this->assertEquals("query 2", $entity->firstName);
    }

    public function testFind()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("query%"));
        $orderBy = self::$db->createOrderBy();
        $orderBy->by($table->id->desc());

        $list = self::$db->find($table, $where, $orderBy);
        $this->assertNotNull($list);
        $this->assertEquals("query 2", $list[0]->firstName);
    }

    public function testFindTop()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("query%"));
        $orderBy = self::$db->createOrderBy();
        $orderBy->by($table->id->desc());

        $list = self::$db->findTop($table, 1, $where, $orderBy);
        $this->assertNotNull($list);
        $this->assertEquals(1, count($list));
        $this->assertEquals("query 2", $list[0]->firstName);
    }

    public function testFindPage()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("query%"));
        $orderBy = self::$db->createOrderBy();
        $orderBy->by($table->id->desc());

        $result = self::$db->findPage($table, 1, 1, $where, $orderBy);
        $this->assertNotNull($result);
        $this->assertEquals(2, $result["totalPages"]);
        $this->assertEquals("query 2", $result["data"][0]->firstName);
    }

    public function testFindCount()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("query%"));
        $orderBy = self::$db->createOrderBy();
        $orderBy->by($table->id->desc());

        $result = self::$db->findCount($table, $where);
        $this->assertEquals(2, $result);
    }

}
