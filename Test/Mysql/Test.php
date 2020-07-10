<?php

namespace LingORM\Test\TestSample;

use LingORM\ORM;
use LingORM\Test\Entity\TestEntity;

class Test
{
    // public function test()
    // {
    //     $this->where();
    // }

    // public function where(){
    //     $db = ORM::db("test");
    //     $table = $db->createTable(new TestEntity());
    //     $where = $db->createWhere();
    //     $where->or($table->id->eq(38), $table->id->eq(39));
    //     $where->orAnd($table->testName->like("name"), $where->getOr($table->testName->eq("my name"), $table->testName->eq("your name")));
    //     $where2 = $db->createWhere();
    //     $where2->or($table->id->gt(0),$table->id->lt(10));
    //     $where->and($where2);
    //     var_dump($where->params);
    //     echo $where->sql; // 输出 (t1.id = :p0 OR t1.id = :p1) AND t1.test_name LIKE :p4 AND (t1.test_name = :p2 OR t1.test_name = :p3)
    //     echo "\n";
    // }

    // public function insert()
    // {
    //     $entity = new TestEntity();
    //     $entity->testName = "my name";
    //     $entity->testTime = "2016-09-01";

    //     $db = ORM::db("test");
    //     $db->insert($entity);
    //     echo "insert success";
    // }

    // public function batchInsert()
    // {
    //     $entityArr = array();
    //     for ($i = 0; $i < 2; $i++) {
    //         $entity = new TestEntity();
    //         $entity->testName = "my name " . $i;
    //         $entity->testTime = new \DateTime();
    //         array_push($entityArr, $entity);
    //     }

    //     $db = ORM::db("test");
    //     $db->begin();
    //     $result = $db->batchInsert($entityArr);
    //     var_dump($result);
    //     $db->commit();
    //     echo "batche insert success";
    // }

    // public function update()
    // {
    //     $entity = new TestEntity();
    //     $entity->id = 1;
    //     $entity->testName = "my name first1";
    //     $entity->testTime = "2016-09-01";

    //     $db = ORM::db("test");
    //     $result = $db->update($entity);
    //     var_dump($result);
    //     echo "update success";
    // }

    // public function batchUpdate()
    // {
    //     $entityArr = array();
    //     for ($i = 1; $i < 3; $i++) {
    //         $entity = new TestEntity();
    //         $entity->id = $i;
    //         $entity->testName = "my name " . $i;
    //         $entity->testTime = "2016-09-02";
    //         array_push($entityArr, $entity);
    //     }

    //     $db = ORM::db("test");
    //     $db->batchUpdate($entityArr);

    //     echo "batch update success";
    // }

    // public function updateBy()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable(new TestEntity());
    //     $wh = $db->createWhere();
    //     $wh->or(
    //         $testTable->id->eq(4)
    //     );
    //     $paramArr = array();
    //     array_push($paramArr, $testTable->testName->eq("update name"));
    //     $result = $db->updateBy($testTable, $paramArr, $wh);
    //     var_dump($result);
    //     exit();
    // }

    // public function delete()
    // {
    //     $entity = new TestEntity();
    //     $entity->id = 3;

    //     $db = ORM::db("test");
    //     $db->delete($entity);
    //     echo "delete success";
    // }

    // public function deleteBy()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable(new TestEntity());
    //     $wh = $db->createWhere();
    //     $wh->or(
    //         $testTable->id->eq(2)
    //     );
    //     $result = $db->deleteBy($testTable, $wh);
    //     var_dump($result);
    //     exit();
    // }

    // public function table()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable($testTable = new TestEntity());
    //     $result = $db->table($testTable)->select($testTable->id, $testTable->testName)
    //         ->where($testTable->id->gt(4))
    //         ->orderBy($testTable->id->asc())
    //         ->find();
    //     var_dump($result);
    //     exit();
    // }

    // public function first()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable($testTable = new TestEntity());
    //     $wh = $db->createWhere();
    //     $wh->or(
    //         $testTable->id->lt(2), $testTable->id->gt(4)
    //     );
    //     $order = $db->createOrderBy()
    //         ->orderBy($testTable->id->desc(), $testTable->testName);

    //     $result = $db->first($testTable, $wh, $order);
    //     var_dump($result);
    //     exit();
    // }

    // public function find()
    // {
    //     $tempTime = microtime(true);
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable($testTable = new TestEntity());
    //     $wh = $db->createWhere();
    //     $wh->or(
    //         $testTable->id->lt(2), $testTable->id->gt(3)
    //     );
    //     $order = $db->createOrderBy()
    //         ->orderBy($testTable->id->desc(), $testTable->testName);

    //     $result = $db->find($testTable, $wh, $order);
    //     var_dump($result[0]);
    //     echo "use time:" . (microtime(true) - $tempTime) . " \n";exit;
    //     exit();
    // }

    // public function selectBuilder()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable($testTable = new TestEntity());
    //     $where = $db->createWhere();
    //     $where->or(
    //         $testTable->id->lt(2), $testTable->id->gt(3)
    //     );

    //     $queryBuilder = $db->createQueryBuilder();
    //     $queryBuilder->select($testTable->id->alias("num"), $testTable, $testTable->testTime)
    //         ->from($testTable)
    //         ->where($where)
    //         ->orderBy($testTable->id->desc());
    //     $result = $queryBuilder->find($testTable);

    //     var_dump($result);
    //     exit();
    // }

    // public function selectBuilderGroup()
    // {
    //     $db = ORM::db("test");
    //     $testTable = $db->createTable($testTable = new TestEntity());
    //     $where = $db->createWhere();
    //     $where->or(
    //         $testTable->id->lt(2), $testTable->id->gt(3)
    //     );

    //     $queryBuilder = $db->createQueryBuilder();
    //     $queryBuilder->select($testTable->id->count()->alias("num"), $testTable->testName->max()->alias("test_name"))
    //         ->from($testTable)
    //         ->where($where)
    //         ->groupBy($testTable->id)
    //         ->orderBy($testTable->id->desc());
    //     $result = $queryBuilder->find();

    //     var_dump($result);
    //     exit();
    // }

    // public function selectPage()
    // {
    //     $db = ORM::db("test");

    //     $testTable = $db->createTable(new TestEntity());

    //     $where = $db->createWhere();
    //     $where->or(
    //         $testTable->id->lt(2), $testTable->id->gt(3)
    //     );

    //     $queryBuilder = $db->createQueryBuilder();
    //     $queryBuilder->select($testTable)
    //         ->from($testTable)
    //         ->where($where)
    //         ->orderBy($testTable->id->desc());
    //     $result = $queryBuilder->findPage(2, 1, $testTable);

    //     var_dump($result);
    //     exit();
    // }

    // public function exeSql()
    // {
    //     $db = ORM::db("test");
    //     $sql = "update test.test set test_name='first name' where id=:id";
    //     $paramArr = array("id" => 1);
    //     $result = $db->createNative()->excute($sql, $paramArr);
    //     var_dump($result);
    //     exit();
    // }

    // public function getSqlResult()
    // {
    //     $db = ORM::db("test");
    //     $sql = "select * from test.test where id=:id";
    //     $paramArr = array("id" => 1);
    //     $pageSize = 10;
    //     $pageIndex = 1;
    //     $result = $db->createNative()->findPage($sql, $paramArr, $pageIndex, $pageSize, new TestEntity());
    //     var_dump($result);
    //     exit();

    // }

}
// require 'E:\github\lingorm-php\AutoLoader\AutoLoader.php';
// putenv("LINGORM_CONFIG=E:/github/lingorm-php/Test/config/database_config.json");
// (new Test())->test();
