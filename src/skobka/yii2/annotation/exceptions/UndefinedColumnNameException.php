<?php
/**
 *
 * @author Soshnikov Artem <213036@skobka.com>
 * @version 1.0
 * @copyright (c) 15.04.2016 13:00
 * @website http://skobka.com
 * @license http://skobka.com/license.html
 * @project annotation-test
 */

namespace skobka\yii2\annotation\exceptions;


class UndefinedColumnNameException extends \Exception
{
    public $message = 'Annotation @Column must contain valid name property';
}