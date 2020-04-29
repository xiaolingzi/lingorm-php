# Data Query

## Where Clause

Example：

```php
$db = ORM::db("test");
$table = $db->createTable(new TestEntity());
$where = $db->createWhere();
$where->or($table->id->eq(38), $table->id->eq(39));
$where->and($table->testName->like("name"));
echo $where->sql; // 输出 (t1.id = :p0 OR t1.id = :p1) AND t1.test_name LIKE :p2
```

With 'CreateWhere', you can create a WHERE clause object. There are four member methods in it. There are 'Or', 'And', 'GetOr' and 'GetAnd'. 'Or' and 'And' return the where object, while 'GetOr' and 'GetAnd' return the where clause string.

```php
$db = ORM::db("test");
$table = $db->createTable(new TestEntity());
$where = $db->createWhere();
$where->or($table->id->eq(38), $table->id->eq(39));
$where->and($table->testName->like("name"), $where->getOr($table->testName->eq("my name"), $table->testName->eq("your name")));
echo $where->sql; // 输出 (t1.id = :p0 OR t1.id = :p1) AND t1.test_name LIKE :p4 AND (t1.test_name = :p2 OR t1.test_name = :p3)
```

The available operators are as follows:

gt | Greater than. Example: table.ID.GE(39)
ge | Greater and equal than.
lt| Less than.
le | Less and equal than.
eq | Equal.
neq | Not Equal.
like
in
nin | Not in.

## Simple Query

Use the 'Table' function

```php
$db = ORM::db("test");
$testTable = $db->createTable($testTable = new TestEntity());
$result = $db->table($testTable)->select($testTable->id, $testTable->testName)
    ->where($testTable->id->gt(4))
    ->orderBy($testTable->id->asc())
    ->find();
```

This 'Where' method can accept the instance created by 'CreateWhere' as argument.
In addition to the 'Find' method. All the available functions are as follows:

```php
first($classObject = null); // return the first row
find($classObject = null); // return all rows
findPage($pageIndex, $pageSize, $classObject = null); // return page result
findCount(); // return the number of rows
```

Other Functions

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

Other functions available:

```php
first($table, AbstractWhere $where, AbstractOrderBy $order = null);
find($table, AbstractWhere $where, AbstractOrderBy $order = null);
findTop($table, $limit, AbstractWhere $where, AbstractOrderBy $order = null);
findPage($table, $pageIndex, $pageSize, AbstractWhere $where, AbstractOrderBy $order = null);
findCount($table, AbstractWhere $where);
```

## Query Builder

Query Builder support for multi-table joint queries. For example:

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

The type of 'Find' function's return value is 'array'. If you want to map the result to an object, you can do it like this:

```php
class Result
{
    public $num;
    public $testName;
}

$result = $queryBuilder->find(new Result());
```
