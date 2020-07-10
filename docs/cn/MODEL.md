# 模型的定义

## 示例及说明

``` php
<?php
namespace LingORM\Test\Entity;

/**
 * @Table (name="test",database="test")
 */
class TestEntity
{
    /**
     * @Column (type="int",isGenerated=1,isPrimary=1)
     */
    public $id;

    /**
     * @Column (name="test_name", type="string")
     */
    public $testName;

    /**
     * @Column (name="test_time", type="datetime")
     */
    public $testTime;
}
```

实体类包含了类和表、属性和表字段之间的映射关系，通过注释中的@Table和@Column参数来表示。

其中@Table的参数如下：
(1) name 表示该实体类对应的表名。可选，如没有该参数，则默认以类名作为表名。
(2) database 表示表所在数据库。可选，如没有该参数，则使用查询是提供的配置或者默认配置中的数据库作为该表的数据库。

@Column参数如下：
(1) name 表示该属性对应的表字段。可选，如没有该参数则默认以该属性名作为字段名。
(2) type 字段类型。可选，默认为string。参数值主要有int,string,float,double,datetime。
(3) isGenerated 字段值是否数据库自动生成。可选，默认为0。如果该值设置为1在插入的时候就不会使用该字段。
(4) isPrimary 是否主键。可选，默认为0.对实体进行删除更新时会使用这些字段作为条件。
(5) length 字段长度。可选，改参数只对string字段使用。

## 实体类的生成

运行Tools目录下的MysqlEntityGenerator.php文件可以将mysql的表生成对应的实体类,使用前需要将里面getConnection方法中的数据库连接改为实际的和将模板命名空间改为实际的。运行命令如下：

```shell
php {your application path}/LingORM/Tools/MysqlEntityGenerator.php -d [database] -t [table] -f [diretory]
```

(1) -d 数据库名。必填。
(2) -t 表名。可选，如没有该参数则默认生成指定数据库中的所有表。
(3) -f 存储的目录。 可选，如果没有则存储在当前目录下的entity目录中。
