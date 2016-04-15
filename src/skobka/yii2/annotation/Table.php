<?php
namespace skobka\yii2\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Table implements Annotation
{
    /**
     * skobka\yii2\annotation\Table name like {{%my_table}}
     * @var string
     */
    public $name;
}