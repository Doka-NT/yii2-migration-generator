<?php

namespace tests\unit\Controllers;

use PHPUnit\Framework\TestCase;
use skobka\yii2\migrationGenerator\commands\CommandInterface;
use skobka\yii2\migrationGenerator\controllers\MigrationGeneratorController;
use skobka\yii2\migrationGenerator\factory\CreateCommandInterface;
use skobka\yii2\migrationGenerator\factory\GeneratorCommandFactory;
use yii\base\Module;

/**
 * @coversDefaultClass skobka\yii2\migrationGenerator\Controllers\MigrationGeneratorController
 */
class MigrationGeneratorControllerTest extends TestCase
{

    /**
     * @covers ::__construct()
     * @dataProvider constructData
     * @param array $config
     * @param string $expected
     */
    public function testConstruct(array $config, string $expected): void
    {
        // random string
        $id = microtime();
        /* @var $module Module|\PHPUnit_Framework_MockObject_MockObject */
        $module = $this
            ->getMockBuilder(Module::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = new MigrationGeneratorController($id, $module, $config);

        $this->assertEquals($expected, $controller->defaultAction);
    }

    /**
     * @covers ::actionGenerate()
     * @dataProvider classAndDirectoryData
     * @param string $class
     * @param string $directory
     * @throws \yii\base\Exception
     */
    public function testActionGenerate(string $class, string $directory)
    {
        /* @var $controller MigrationGeneratorController|\PHPUnit_Framework_MockObject_MockObject */
        $controller = $this
            ->getMockBuilder(MigrationGeneratorController::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getMigrationDirectory',
                'prepareDirectory',
                'executeGenerateCommand',
                'logMessage'
            ])
            ->getMock();

        $controller->method('getMigrationDirectory')->willReturn($directory);
        $controller->expects($this->once())->method('prepareDirectory')->with($directory);
        $controller->expects($this->once())->method('executeGenerateCommand')->with($class, $directory);
        $controller->expects($this->once())->method('logMessage')->with("Migration for $class was successfully generated");

        $result = $controller->actionGenerate($class);

        $this->assertEquals(MigrationGeneratorController::EXIT_CODE_NORMAL, $result);
    }

    /**
     * @covers ::executeGenerateCommand()
     * @dataProvider classAndDirectoryData
     * @param string $class
     * @param string $directory
     */
    public function testExecuteGenerateCommand(string $class, string $directory)
    {
        /* @var $controller MigrationGeneratorController|\PHPUnit_Framework_MockObject_MockObject */
        $controller = $this
            ->getMockBuilder(MigrationGeneratorController::class)
            ->disableOriginalConstructor()
            ->setMethods(['createGenerateCommand'])
            ->getMock();

        /* @var $command CommandInterface|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(CommandInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $controller->method('createGenerateCommand')->with($class, $directory)->willReturn($command);
        $command->expects($this->once())->method('execute');

        $reflection = new \ReflectionMethod(MigrationGeneratorController::class, 'executeGenerateCommand');
        $reflection->setAccessible(true);

        $reflection->invoke($controller, $class, $directory);
    }

    /**
     * @covers ::createGenerateCommand()
     * @dataProvider classAndDirectoryData
     * @param string $class
     * @param string $directory
     */
    public function testCreateGenerateCommand(string $class, string $directory)
    {
        /* @var $controller MigrationGeneratorController|\PHPUnit_Framework_MockObject_MockObject */
        $controller = $this
            ->getMockBuilder(MigrationGeneratorController::class)
            ->disableOriginalConstructor()
            ->setMethods(['createCommandFactory'])
            ->getMock();

        /* @var $factory CreateCommandInterface|\PHPUnit_Framework_MockObject_MockObject */
        $factory = $this
            ->getMockBuilder(CreateCommandInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /* @var $command CommandInterface|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(CommandInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller->method('createCommandFactory')->with($class, $directory)->willReturn($factory);
        $factory->method('create')->willReturn($command);

        $reflection = new \ReflectionMethod(MigrationGeneratorController::class, 'createGenerateCommand');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($controller, $class, $directory);

        $this->assertSame($command, $result);
    }

    /**
     * @covers ::createCommandFactory()
     * @uses \skobka\yii2\migrationGenerator\factory\GeneratorCommandFactory
     *
     * @dataProvider classAndDirectoryData
     * @param string $class
     * @param string $directory
     */
    public function testCreateCommandFactory(string $class, string $directory)
    {
        /* @var $controller MigrationGeneratorController|\PHPUnit_Framework_MockObject_MockObject */
        $controller = $this
            ->getMockBuilder(MigrationGeneratorController::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $reflection = new \ReflectionMethod(MigrationGeneratorController::class, 'createCommandFactory');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($controller, $class, $directory);

        $this->assertInstanceOf(CreateCommandInterface::class, $result);
        $this->assertInstanceOf(GeneratorCommandFactory::class, $result);
    }

    /**
     * Test data provider
     * @return array
     */
    public function constructData(): array
    {
        return [
            [
                'config' => [],
                'expected' => 'generate',
            ],
            [
                'config' => [
                    'defaultAction' => 'foo',
                ],
                'expected' => 'foo',
            ],
        ];
    }

    /**
     * Test data provider
     * @return array
     */
    public function classAndDirectoryData(): array
    {
        return [
            [
                'class' => 'FooSomeClass',
                'directory' => 'FooSomeDirectory',
            ],
            [
                'class' => 'BarSomeClass',
                'directory' => 'BarSomeDirectory',
            ],
        ];
    }
}
