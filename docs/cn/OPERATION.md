# 数据的增删改

## 插入

### 单条插入

```php
$entity = new TestEntity();
$entity->testName = "my name";
$entity->testTime = "2016-09-01";

$db = ORM::db("test");
$db->insert($entity);
```

### 批量插入

```php
$entityArr = array();
for ($i = 0; $i < 2; $i++) {
    $entity = new TestEntity();
    $entity->testName = "my name " . $i;
    $entity->testTime = new \DateTime();
    array_push($entityArr, $entity);
}

$db = ORM::db("test");
$result = $db->batchInsert($entityArr);
```

## 更新

### 单条更新

```php
$entity = new TestEntity();
$entity->id = 1;
$entity->testName = "my name first1";
$entity->testTime = "2016-09-01";

$db = ORM::db("test");
$affected = $db->update($entity);
```

### 批量更新

```php
$db->batchUpdate($entityArr);
```

### 条件更新

```php
$db = ORM::db("test");
$testTable = $db->createTable(new TestEntity());
$wh = $db->createWhere();
$wh->or(
    $testTable->id->eq(4)
);
$paramArr = array();
array_push($paramArr, $testTable->testName->eq("update name"));
$affected = $db->updateBy($testTable, $paramArr, $wh);
```

## 删除

### 单条删除

```php
$entity = new TestEntity();
$entity->id = 3;

$db = ORM::db("test");
$db->delete($entity);
```

### 条件删除

```php
$db = ORM::db("test");
$testTable = $db->createTable(new TestEntity());
$wh = $db->createWhere();
$wh->or(
    $testTable->id->eq(2)
);
$result = $db->deleteBy($testTable, $wh);
```
