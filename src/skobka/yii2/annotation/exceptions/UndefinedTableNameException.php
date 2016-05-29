<?php
/**
 *
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 15.04.2016 13:02
 */

namespace skobka\yii2\annotation\exceptions;

class UndefinedTableNameException extends \Exception
{
    public $message = 'Annotation @Table must contain valid name property';
}
