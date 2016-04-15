<?php
/**
 *
 * @author Soshnikov Artem <213036@skobka.com>
 * @version 1.0
 * @copyright (c) 15.04.2016 13:33
 * @website http://skobka.com
 * @license http://skobka.com/license.html
 * @project annotation-test
 */

namespace skobka\yii2\Controllers;

use skobka\yii2\migration\Generator;
use yii\console\Controller;

class MigrationGeneratorController extends Controller
{
    public $defaultAction = 'generate';

    public $migrationsDir = '@app/migrations';

    public function actionGenerate($class){
        $generator = new Generator;
        $dir = \Yii::getAlias($this->migrationsDir);
        $generator->generate($class, $dir);
        $this->stdout("Migration for $class was successfully generated");
    }
}