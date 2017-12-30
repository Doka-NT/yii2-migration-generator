# yii2-migration-generator

[![Build Status](https://travis-ci.org/Doka-NT/yii2-migration-generator.svg?branch=master)](https://travis-ci.org/Doka-NT/yii2-migration-generator)
[![codecov](https://codecov.io/gh/Doka-NT/yii2-migration-generator/branch/master/graph/badge.svg)](https://codecov.io/gh/Doka-NT/yii2-migration-generator)

Generates migrations from Annotations of Model or ActiveRecords classes (not only). You simple write classes as usual and then asking generator to create migration file from code.

**NOTE!** Works only with yii2 console. This is **NOT Gii extension**

## Installation 

```bash
php composer.phar require --dev skobka/yii2-migration-generator "*"
```
Or add it manualy to composer.json
```json
{
  "require-dev": {
    "skobka/yii2-migration-generator": "*"
  }
}
```

Next step is to enable generation from Yii2 console. Add following code in console/config/main.php
```php
use skobka\yii2\migrationGenerator\Controllers\MigrationGeneratorController;

return [
   //...
    'controllerMap' => [
        'migration' => [
            'class' => MigrationGeneratorController::class,
        ],
    ],
   //...
]
```

## Usage
This package provide following annotations:
- @Superclass(active=true)
- @Table(name="")
- @Column(name="", type="", typeArgs={}, extra="")

### @Superclass
Indicate that annotation from current class must be included in child class. When parser goes throught classes it will skip class without @Superclass or @Table annotation. Parser will not finds parents of such class. 
If you want to skip current @Superclass from parsing, simple set option *active* to *false*

### @Table
This annotations tells that current class define a table. Table name takes from property name:
```
@Table(name="my_first_table")
// You can use @Table() without parameters, then MyModel::tableName() will be used
```

### @Column
This annotation defined a table column. You must specify the name and type of column. 

**NOTE!** Column types must be a method names of yii\db\SchemaBuilderTrait
Available properties:
- **name** - name of column
- **type** - column type
- **typeArgs** - an array of type arguments, for example: @Column(type="decimal", typeArgs={10,2})
- **extra** - string wich will be append to then end of column definition 


After all run
```bash
php yii migration/generate common/models/mymodel 
```

## Examples
```php
/**
 * @Superclass()
 * @Column(name="id", type="primaryKey")
 * @Column(name="uid", type="integer", notNull=false)
 * 
 * @property int id
 * @property int uid
 */
class BaseModel
{

}
```

```php
<?php
use skobka\yii2\migrationGenerator\annotation\Column;
use skobka\yii2\migrationGenerator\annotation\Superclass;
use skobka\yii2\migrationGenerator\annotation\Table;

/**
 * @Table(name="{{%simple_class}}")
 *
 * @Column(name="some", type="string", typeArgs={255}, extra=" DEFAULT '0'")
 * @Superclass()
 * use superclass annotation to allow extending class annotation
 *
 * @property string some
 */
class SimpleClass extends BaseModel
{

}
```

```php
<?php
use skobka\yii2\migrationGenerator\annotation\Column;
use skobka\yii2\migrationGenerator\annotation\Table;

/**
 * @Table(name="sub_simple_class")
 * @Column(name="created", type="integer", notNull=true)
 *
 * @property int created
 */
class SubSimpleClass extends SimpleClass
{

}
```
 





