<?php
/**
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 15.04.2016 11:42
 */

namespace skobka\yii2\migrationGenerator\annotation\exceptions;

class OneTableAnnotationAllowedException extends \Exception
{
    public $message = 'Only one @Table annotation allowed for class';
}
