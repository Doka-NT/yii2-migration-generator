<?php

namespace skobka\yii2\migrationGenerator\factory;

use Exceptions\Operation\OperationException;
use Exceptions\Operation\UnexpectedException;
use skobka\yii2\migrationGenerator\commands\CommandInterface;
use skobka\yii2\migrationGenerator\commands\GenerateCommand;
use skobka\yii2\migrationGenerator\migration\Generator;
use yii\db\Schema;

/**
 * Factory
 */
class GeneratorCommandFactory implements CreateCommandInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $directory;

    /**
     * GeneratorCommandFactory constructor.
     *
     * @param string $className
     * @param string $directory
     */
    public function __construct(string $className, string $directory)
    {
        $this->className = $className;
        $this->directory = $directory;
    }

    /**
     * Creates executable command
     *
     * @return CommandInterface
     * @throws OperationException
     */
    public function create(): CommandInterface
    {
        try {
            return new GenerateCommand(
                $this->createGenerator(),
                $this->getSchema(),
                $this->getClassName(),
                $this->getDirectory()
            );
        } catch (\Exception $e) {
            throw new UnexpectedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return string
     */
    protected function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    protected function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return Generator
     */
    protected function createGenerator(): Generator
    {
        return new Generator();
    }

    /**
     * @return Schema
     * @throws \yii\base\NotSupportedException
     */
    protected function getSchema(): Schema
    {
        return \Yii::$app->getDb()->getSchema();
    }
}
