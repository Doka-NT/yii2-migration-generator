<?php
/**
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 15.04.2016 13:33
 */

namespace skobka\yii2\migrationGenerator\controllers;

use skobka\yii2\migrationGenerator\commands\CommandInterface;
use skobka\yii2\migrationGenerator\factory\CreateCommandInterface;
use skobka\yii2\migrationGenerator\factory\GeneratorCommandFactory;
use yii\base\Module;
use yii\console\Controller;
use yii\helpers\BaseFileHelper;
use yii\helpers\Console;

/**
 * Console command controller
 */
class MigrationGeneratorController extends Controller
{
    /**
     * Directory containing migrations
     *
     * @var string
     */
    public $migrationsDir = '@app/migrations';

    /**
     * MigrationGeneratorController constructor.
     *
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct(string $id, Module $module, array $config = [])
    {
        $this->defaultAction = 'generate';

        parent::__construct($id, $module, $config);
    }

    /**
     * Generate migration
     *
     * @param string $class
     * @return int
     * @throws \yii\base\InvalidParamException
     * @throws \Exceptions\Operation\OperationException
     * @throws \yii\base\Exception
     */
    public function actionGenerate(string $class): int
    {
        $directory = $this->getMigrationDirectory();

        $this->prepareDirectory($directory);
        $this->executeGenerateCommand($class, $directory);

        $this->logMessage("Migration for $class was successfully generated");

        return static::EXIT_CODE_NORMAL;
    }

    /**
     * @param string $class
     * @param string $directory
     * @throws \Exceptions\Operation\OperationException
     */
    protected function executeGenerateCommand(string $class, string $directory): void
    {
        $this
            ->createGenerateCommand($class, $directory)
            ->execute();
    }

    /**
     * @param string $className
     * @param string $directory
     * @return CommandInterface
     * @throws \Exceptions\Operation\OperationException
     */
    protected function createGenerateCommand(string $className, string $directory): CommandInterface
    {
        $factory = $this->createCommandFactory($className, $directory);

        return $factory->create();
    }

    /**
     * @param string $className
     * @param string $directory
     * @return CreateCommandInterface
     */
    protected function createCommandFactory(string $className, string $directory): CreateCommandInterface
    {
        return new GeneratorCommandFactory($className, $directory);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    protected function getMigrationDirectory(): string
    {
        return \Yii::getAlias($this->migrationsDir);
    }

    /**
     * @param string $directory
     * @throws \yii\base\Exception
     */
    protected function prepareDirectory(string $directory)
    {
        BaseFileHelper::createDirectory($directory);
    }

    /**
     * @param $message
     */
    protected function logError(string $message): void
    {
        Console::error($message);
    }

    /**
     * @param $message
     */
    protected function logMessage($message): void
    {
        Console::output($message);
    }
}
