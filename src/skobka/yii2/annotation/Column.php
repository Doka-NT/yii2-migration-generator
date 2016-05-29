<?php
/**
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 29.05.2016
 */

namespace skobka\yii2\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Column implements Annotation
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     * @Enum({"primaryKey", "bigPrimaryKey", "string", "text", "smallInteger", "integer", "bigInteger", "float", "double", "decimal", "dateTime", "timestamp", "time", "date", "binary", "boolean", "money"})
     */
    public $type;

    /**
     * @var array
     */
    public $typeArgs = [];

    /**
     * @var bool
     */
    public $notNull = false;

    /**
     * @var string
     */
    public $extra = '';
}
