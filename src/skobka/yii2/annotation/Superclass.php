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
class Superclass implements Annotation
{
    /**
     * @var bool
     */
    public $active = true;
}
