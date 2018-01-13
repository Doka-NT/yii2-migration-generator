<?php
/**
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 29.05.2016
 */

namespace skobka\yii2\migrationGenerator\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Table implements Annotation
{
    /**
     * Table name like {{%my_table}}
     * @var string
     */
    public $name;
}
