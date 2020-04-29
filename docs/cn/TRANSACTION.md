# 事务的使用

示例如下：

```php
$db = ORM::db("test");
$db->begin();
// do something here...
$db->commit();
// $db->rollback();
```

需要注意的是，同一个事务必须在同一个ORM::db("test")创建的实例下执行以上三个方法。
