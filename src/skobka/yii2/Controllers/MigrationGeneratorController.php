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
use yii\db\ActiveRecord;
use yii\helpers\BaseFileHelper;
use yii\helpers\Console;

class MigrationGeneratorController extends Controller
{
    public $defaultAction = 'generate';

    public $migrationsDir = '@app/migrations';

    public function actionGenerate($class){
        $generator = new Generator;
        $dir = \Yii::getAlias($this->migrationsDir);
        BaseFileHelper::createDirectory($dir);

        if(!is_subclass_of($class, ActiveRecord::class)){
            Console::error("You must provide an ActiveRecord subclass");
            return static::EXIT_CODE_ERROR;
        }
        /* @var $class ActiveRecord */
        $tableName = $class::tableName();
        $tableSchema = \Yii::$app->db->getSchema()->getTableSchema($tableName);
        
        $generator->generate($class, $dir, $tableSchema);
        Console::output("Migration for $class was successfully generated");

        return static::EXIT_CODE_NORMAL;
    }
}