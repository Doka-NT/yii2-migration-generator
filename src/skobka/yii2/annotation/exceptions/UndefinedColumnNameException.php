<?php
/**
 *
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 15.04.2016 13:00
 */

namespace skobka\yii2\annotation\exceptions;

class UndefinedColumnNameException extends \Exception
{
    public $message = 'Annotation @Column must contain valid name property';
}
