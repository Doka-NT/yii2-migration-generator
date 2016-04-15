<?php

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