<?php
namespace LingORM\Test\Mysql;

use LingORM\Test\Entity\FirstTableEntity;
use LingORM\Test\Entity\SecondTableEntity;
use PHPUnit\Framework\TestCase;

require_once "Base.php";

class QueryBuilderTest extends TestCase
{
    private static $db;
    public static function setUpBeforeClass(): void
    {
        self::$db = (new Base())->db();
        self::$db->begin();
        $entityArr = array();
        for ($i = 1; $i < 3; $i++) {
            $entity = new FirstTableEntity();
            $entity->firstName = "first query " . $i;
            $entity->firstNumber = 2000 + $i;
            $entity->firstTime = "2019-09-02";
            array_push($entityArr, $entity);
        }
        self::$db->batchInsert($entityArr);

        $entityArr = array();
        for ($i = 1; $i < 3; $i++) {
            $entity = new SecondTableEntity();
            $entity->secondName = "second query " . $i;
            $entity->secondNumber = 2000 + $i;
            $entity->secondTime = "2019-09-02";
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
        $builder = self::$db->queryBuilder();
        $entity = $builder->from($table)
            ->select($table->firstName, $table->firstNumber)
            ->where($table->firstName->like("first query%"))
            ->first(new FirstTableEntity());
        $this->assertNotNull($entity);
        $this->assertEquals(2001, $entity->firstNumber);
    }

    public function testFind()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $secTable = self::$db->createTable(new SecondTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("first query 1"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $where->orAnd($table->firstName->like("first query 2"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $builder = self::$db->queryBuilder();
        $builder = $builder->from($table)
            ->select($table->firstNumber, $table->firstName->i()->max()->alias("first_name"), $secTable->secondName->i()->f("MAX")->alias("second_name"))
            ->leftJoin($secTable, $table->firstNumber->eq($secTable->secondNumber))
            ->groupBy($table->firstNumber)
            ->where($where)
            ->orderBy($table->firstNumber->desc())
            ->limit(2);
        $list = $builder->find();
        $this->assertNotNull($list);
        $this->assertEquals(2, count($list));
        $this->assertEquals("second query 2", $list[0]["second_name"]);

        $list = $builder->find(new Result());
        $this->assertEquals("second query 2", $list[0]->secondName);
    }

    public function testFindPage()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $secTable = self::$db->createTable(new SecondTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("first query 1"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $where->orAnd($table->firstName->like("first query 2"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $builder = self::$db->queryBuilder();
        $builder = $builder->from($table)
            ->select($table->firstName, $table->firstNumber, $secTable->secondName)
            ->innerJoin($secTable, $table->firstNumber->eq($secTable->secondNumber))
            ->where($where)
            ->orderBy($table->id->desc())
            ->limit(2);
        $result = $builder->findPage(1, 1);
        $this->assertNotNull($result);
        $this->assertEquals(2, $result["totalPages"]);
        $this->assertEquals("second query 2", $result["data"][0]["second_name"]);

        $result = $builder->findPage(1, 1, new Result());
        $this->assertEquals("second query 2", $result["data"][0]->secondName);
    }

    public function testFindCount()
    {
        $table = self::$db->createTable(new FirstTableEntity());
        $secTable = self::$db->createTable(new SecondTableEntity());
        $where = self::$db->createWhere();
        $where->and($table->firstName->like("first query 1"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $where->orAnd($table->firstName->like("first query 2"), $table->firstNumber->ge(2001), $table->firstNumber->le(2002));
        $builder = self::$db->queryBuilder();
        $builder = $builder->from($table)
            ->select($table->firstName, $table->firstNumber, $secTable->secondName)
            ->rightJoin($secTable, $table->firstNumber->eq($secTable->secondNumber))
            ->where($where)
            ->orderBy($table->id->desc());
        $result = $builder->findCount();
        $this->assertEquals(2, $result);
    }

}

class Result
{
    /**
     * @Column (name="first_name", type="string")
     */
    public $firstName;
    /**
     * @Column (name="second_name", type="string")
     */
    public $secondName;
}
