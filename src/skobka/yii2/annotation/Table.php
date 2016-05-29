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
class Table implements Annotation
{
    /**
     * skobka\yii2\annotation\Table name like {{%my_table}}
     * @var string
     */
    public $name;
}
