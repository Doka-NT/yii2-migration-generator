<?php

namespace skobka\yii2\migrationGenerator\factory;

use Exceptions\Operation\OperationException;
use skobka\yii2\migrationGenerator\commands\CommandInterface;

/**
 * Command factory interface
 */
interface CreateCommandInterface
{
    /**
     * Creates executable command
     *
     * @return CommandInterface
     * @throws OperationException
     */
    public function create(): CommandInterface;
}
