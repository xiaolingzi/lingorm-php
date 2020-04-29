# 原生SQL支持

示例：

```php
$db = ORM::db("test");
$sql = "select * from test.test where id=:id";
$paramArr = array("id" => 1);
$pageSize = 10;
$pageIndex = 1;
$result = $db->createNative()->findPage($sql, $paramArr, $pageIndex, $pageSizes);
```

如果需要将结果映射到结构体，就如下：

```php
$result = $db->createNative()->findPage($sql, $paramArr, $pageIndex, $pageSize, new TestEntity());
```

*需要注意的是，这里的参数化查询采用的命名参数的方式而不是问号。

所有支持的方法如下：

```php
find($sql, $paramArr, $classObject = null); // 查询列表时使用
findPage($sql, $paramArr, $pageIndex, $pageSize, $classObject = null); // 查询分页数据时使用
findCount($sql, $paramArr); // 查询数量时使用
first($sql, $paramArr, $classObject = null); // 返回符合条件的第一条
excute($sql, $paramArr); // 执行增、删、改时使用，返回影响条数
```
