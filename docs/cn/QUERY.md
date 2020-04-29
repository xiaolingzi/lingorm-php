# 数据查询

## 查询条件构建

我们先来看一个示例：

```php
$db = ORM::db("test");
$table = $db->createTable(new TestEntity());
$where = $db->createWhere();
$where->or($table->id->eq(38), $table->id->eq(39));
$where->and($table->testName->like("name"));
echo $where->sql; // 输出 (t1.id = :p0 OR t1.id = :p1) AND t1.test_name LIKE :p2
```

通过CreateWhere方法可以构建一个where对象，where对象提供Or和And方法，这两个方法就分别对应数据库中的or和and。而且这两个方法都是接受多个参数。
除此之外，where对象还提供了GetOr和GetAnd方法，参数跟Or和And一样，但是他们是返回当前的条件字符串，用于一些复杂的条件拼接。例如：

```php
$db = ORM::db("test");
$table = $db->createTable(new TestEntity());
$where = $db->createWhere();
$where->or($table->id->eq(38), $table->id->eq(39));
$where->and($table->testName->like("name"), $where->getOr($table->testName->eq("my name"), $table->testName->eq("your name")));
echo $where->sql; // 输出 (t1.id = :p0 OR t1.id = :p1) AND t1.test_name LIKE :p4 AND (t1.test_name = :p2 OR t1.test_name = :p3)
```

条件中比较运算符有如下：
gt 大于。比如 $table->id->eq(38)
ge 大于等于
lt 小于
le 小于等于
eq 等于
neq 不等于
like 模糊匹配
in 对应sql中的in
nin 对应sql中的not in

## 简单查询

1）通过Table方法进行链式调用

```php
$db = ORM::db("test");
$testTable = $db->createTable($testTable = new TestEntity());
$result = $db->table($testTable)->select($testTable->id, $testTable->testName)
    ->where($testTable->id->gt(4))
    ->orderBy($testTable->id->asc())
    ->find();
```

其中Where方法可以还可以接收CreateWhere创建的where对象以支持复杂的查询条件。Find方法返回的是一个列表，已经映射到模型中。
除了Find方法之外，还有以下几个方法：

```php
first($classObject = null); // 返回符合条件的第一条数据
find($classObject = null); // 返回符合条件的数据列表
findPage($pageIndex, $pageSize, $classObject = null); //返回分页数据
findCount(); // 返回数量
```

2）直接查询

```php
$db = ORM::db("test");
$testTable = $db->createTable($testTable = new TestEntity());
$wh = $db->createWhere();
$wh->or(
    $testTable->id->lt(2), $testTable->id->gt(3)
);
$order = $db->createOrderBy()
    ->orderBy($testTable->id->desc(), $testTable->testName);

$result = $db->find($testTable, $wh, $order);
```

类似的也有以下几个方法

```php
first($table, AbstractWhere $where, AbstractOrderBy $order = null);
find($table, AbstractWhere $where, AbstractOrderBy $order = null);
findTop($table, $limit, AbstractWhere $where, AbstractOrderBy $order = null);
findPage($table, $pageIndex, $pageSize, AbstractWhere $where, AbstractOrderBy $order = null);
findCount($table, AbstractWhere $where);
```

## 复杂查询

复杂查询支持多表联合查询。示例：

```php
$db = ORM::db("test");
$testTable = $db->createTable($testTable = new TestEntity());
$where = $db->createWhere();
$where->or(
    $testTable->id->lt(2), $testTable->id->gt(3)
);

$queryBuilder = $db->createQueryBuilder();
$queryBuilder->select($testTable->id->count()->alias("num"), $testTable->testName->max()->alias("testName"))
    ->from($testTable)
    ->where($where)
    // ->where($testTable->id->gt(3))
    ->groupBy($testTable->id)
    ->orderBy($testTable->id->desc());
$result = $queryBuilder->find();
```

其中Find方法默认返回的是数组类型，如果需要映射到对象，则将定义的对象实例传递给Find方法，比如

```php
class Result
{
    public $num;
    public $testName;
}

$result = $queryBuilder->find(new Result());
```
