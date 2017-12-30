<?php

namespace skobka\yii2\migrationGenerator\commands;

use Exceptions\Operation\OperationException;

/**
 * Implementation of command pattern
 */
interface CommandInterface
{
    /**
     * Execute command
     * @throws OperationException
     */
    public function execute(): void;
}
