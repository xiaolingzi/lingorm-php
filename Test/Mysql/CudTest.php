<?php
namespace LingORM\Test\Mysql;

use LingORM\Test\Entity\FirstTableEntity;
use PHPUnit\Framework\TestCase;

require_once "Base.php";

class CudTest extends TestCase
{
    private static $db;
    public static function setUpBeforeClass()
    {
        self::$db = (new Base())->db();
        self::$db->begin();
    }

    public static function tearDownAfterClass()
    {
        self::$db->rollback();
        self::$db = null;
    }

    // public function setUp()
    // {
    //     $this->db = $this->db();
    //     $this->db->begin();
    // }

    // public function tearDown()
    // {
    //     $this->db->rollback();
    // }

    public function testInsert()
    {
        $entity = new FirstTableEntity();
        $entity->firstName = "my name";
        $entity->firstNumber = 1001;
        $entity->firstTime = "2016-09-01";

        $id = self::$db->insert($entity);
        $this->assertGreaterThan(0, $id);
    }

    public function testBatchInsert()
    {
        $entityArr = array();
        for ($i = 1; $i < 3; $i++) {
            $entity = new FirstTableEntity();
            $entity->id = $i;
            $entity->firstName = "my name " . $i;
            $entity->firstNumber = 1002;
            $entity->firstTime = "2016-09-02";
            array_push($entityArr, $entity);
        }
        $affectedRows = self::$db->batchInsert($entityArr);
        $this->assertEquals(2, $affectedRows);
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $entity = self::$db->table($table)->where($table->firstName->eq("my name"))->first();
        $this->assertNotEmpty($entity);
        $entity->firstName = "single update";
        $affectedRows = self::$db->update($entity);
        $this->assertEquals(1, $affectedRows);
    }

    /**
     * @depends testBatchInsert
     */
    public function testBatchUpdate()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $entityArr = self::$db->table($table)->where($table->firstNumber->eq(1002))->find();
        $this->assertEquals(2, count($entityArr));

        for ($i = 0; $i < count($entityArr); $i++) {
            $entityArr[$i]->firstName = "batch update " . $i;
        }

        $affectedRows = self::$db->batchUpdate($entityArr);
        $this->assertEquals(2, $affectedRows);
    }

    /**
     * @depends testBatchUpdate
     */
    public function testUpdateBy()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and(
            $table->firstNumber->eq(1002),
            $table->firstName->like("batch update%")
        );
        $paramArr = array(
            $table->firstName->eq("update by")
        );
        $affectedRows = self::$db->updateBy($table, $paramArr, $where);
        $this->assertEquals(2, $affectedRows);
    }

    /**
     * @depends testUpdate
     */
    public function testDelete()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $entity = self::$db->table($table)->where($table->firstNumber->eq(1001))->first();
        $this->assertNotEmpty($entity);
        $affectedRows = self::$db->delete($entity);
        $this->assertEquals(1, $affectedRows);
    }

    /**
     * @depends testUpdateBy
     */
    public function testDeleteBy()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $where = self::$db->createWhere();
        $where->and(
            $table->firstNumber->eq(1002),
            $table->firstName->like("update by%")
        );
        $affectedRows = self::$db->deleteBy($table, $where);
        $this->assertEquals(2, $affectedRows);
    }
}
