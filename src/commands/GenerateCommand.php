<?php

namespace skobka\yii2\migrationGenerator\commands;

use Exceptions\Operation\InvalidOperationException;
use Exceptions\Operation\OperationException;
use skobka\yii2\migrationGenerator\migration\Generator;
use yii\db\ActiveRecord;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * Migration generation command
 */
class GenerateCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $targetClass;

    /**
     * @var string
     */
    private $migrationsDir;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * GenerateCommand constructor.
     *
     * @param Generator $generator
     * @param Schema $schema
     * @param string $targetClass
     * @param string $migrationsDir
     */
    public function __construct(
        Generator $generator,
        Schema $schema,
        string $targetClass,
        string $migrationsDir
    ) {
        $this->generator = $generator;
        $this->schema = $schema;
        $this->targetClass = $targetClass;
        $this->migrationsDir = $migrationsDir;
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $className = $this->getTargetClass();
        $dir = $this->getMigrationDirectory();
        $tableSchema = $this->getTableSchema();

        $this->executeGenerator($className, $dir, $tableSchema);
    }

    /**
     * @param string $className
     * @param string $directory
     * @param TableSchema $tableSchema
     */
    protected function executeGenerator(string $className, string $directory, TableSchema $tableSchema): void
    {
        $generator = $this->getGenerator();
        $generator->generate($className, $directory, $tableSchema);
    }

    /**
     * @return \yii\db\TableSchema
     * @throws OperationException
     */
    protected function getTableSchema(): TableSchema
    {
        $tableName = $this->getTableName();

        return $this
            ->getSchema()
            ->getTableSchema($tableName);
    }

    /**
     * @return string
     * @throws \Exceptions\Operation\InvalidOperationException
     */
    protected function getTableName(): string
    {
        $className = $this->getTargetClass();

        if (!is_subclass_of($className, ActiveRecord::class)) {
            $message = sprintf('You must provide an ActiveRecord subclass. %s given', $className);
            throw new InvalidOperationException($message);
        }

        /* @var $className ActiveRecord */
        return $className::tableName();
    }

    /**
     * @return string
     */
    protected function getTargetClass(): string
    {
        return $this->targetClass;
    }

    /**
     * @return string
     */
    protected function getMigrationDirectory(): string
    {
        return $this->migrationsDir;
    }

    /**
     * @return \yii\db\Schema
     */
    protected function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return Generator
     */
    protected function getGenerator(): Generator
    {
        return $this->generator;
    }
}
