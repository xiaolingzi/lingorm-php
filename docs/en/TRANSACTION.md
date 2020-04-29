# Transaction

Here's an example:

```php
$db = ORM::db("test");
$db->begin();
// do something here...
$db->commit();
// $db->rollback();
```

It is important to note that the same transaction must be in the same db connection. The three methods are executed under an instance created by the ORM::db("test").
