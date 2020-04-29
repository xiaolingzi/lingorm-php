# Native SQL

For example:

```php
$db = ORM::db("test");
$sql = "select * from test.test where id=:id";
$paramArr = array("id" => 1);
$pageSize = 10;
$pageIndex = 1;
$result = $db->createNative()->findPage($sql, $paramArr, $pageIndex, $pageSizes);
```

If you want to map the results to a structure object, you can do it like this:

```php
$result = $db->createNative()->findPage($sql, $paramArr, $pageIndex, $pageSize, new TestEntity());
```

All available methods are as follows:

```php
find($sql, $paramArr, $classObject = null); // return all rows
findPage($sql, $paramArr, $pageIndex, $pageSize, $classObject = null); // return page result
findCount($sql, $paramArr); // return the number of rows
first($sql, $paramArr, $classObject = null); // return the first row
excute($sql, $paramArr); // execute the insert, update and delete sql， return the number of rows affected.
```
