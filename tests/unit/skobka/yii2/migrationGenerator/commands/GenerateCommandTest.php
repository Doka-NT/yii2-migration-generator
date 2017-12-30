<?php

namespace tests\unit\skobka\yii2\migrationGenerator\commands;

use Exceptions\Operation\InvalidOperationException;
use skobka\yii2\migrationGenerator\commands\GenerateCommand;
use PHPUnit\Framework\TestCase;
use skobka\yii2\migrationGenerator\migration\Generator;
use yii\db\ActiveRecord;
use yii\db\Schema;
use yii\db\TableSchema;

/**
 * @coversDefaultClass \skobka\yii2\migrationGenerator\commands\GenerateCommand
 */
class GenerateCommandTest extends TestCase
{
    /**
     * @covers ::__construct()
     */
    public function testConstruct()
    {
        /* @var $generator Generator|\PHPUnit_Framework_MockObject_MockObject */
        $generator = $this
            ->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $schema Schema|\PHPUnit_Framework_MockObject_MockObject */
        $schema = $this
            ->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $targetClass = $this->getRandomString();
        $migrationsDir = $this->getRandomString();

        $command = new GenerateCommand(
            $generator,
            $schema,
            $targetClass,
            $migrationsDir
        );

        $this->assertPropertyEquals($command, 'generator', $generator);
        $this->assertPropertyEquals($command, 'schema', $schema);
        $this->assertPropertyEquals($command, 'targetClass', $targetClass);
        $this->assertPropertyEquals($command, 'migrationsDir', $migrationsDir);
    }

    /**
     * @covers ::execute()
     */
    public function testExecute()
    {
        $targetClass = $this->getRandomString();
        $directory = $this->getRandomString();

        /* @var $command GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTargetClass', 'getMigrationDirectory', 'getTableSchema', 'executeGenerator'])
            ->getMock();

        /* @var $schema TableSchema|\PHPUnit_Framework_MockObject_MockObject */
        $schema = $this
            ->getMockBuilder(TableSchema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $command->method('getTargetClass')->willReturn($targetClass);
        $command->method('getMigrationDirectory')->willReturn($directory);
        $command->method('getTableSchema')->willReturn($schema);

        $command->expects($this->once())->method('executeGenerator')->with($targetClass, $directory, $schema);

        $command->execute();
    }

    /**
     * @covers ::executeGenerator()
     */
    public function testExecuteGenerator()
    {
        $className = $this->getRandomString();
        $directory = $this->getRandomString();

        /* @var $command GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGenerator'])
            ->getMock();

        /* @var $generator Generator|\PHPUnit_Framework_MockObject_MockObject */
        $generator = $this
            ->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        /* @var $tableSchema TableSchema|\PHPUnit_Framework_MockObject_MockObject */
        $tableSchema = $this
            ->getMockBuilder(TableSchema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $command->method('getGenerator')->willReturn($generator);
        $generator->expects($this->once())->method('generate')->with($className, $directory, $tableSchema);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'executeGenerator');
        $reflection->setAccessible(true);

        $reflection->invoke($command, $className, $directory, $tableSchema);
    }

    /**
     * @covers ::getTargetClass()
     */
    public function testGetTargetClass()
    {
        $expected = $this->getRandomString();

        /* @var $object GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $property = new \ReflectionProperty(GenerateCommand::class, 'targetClass');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getTargetClass');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getMigrationDirectory()
     */
    public function testGetMigrationDirectory()
    {
        $expected = $this->getRandomString();

        /* @var $object GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $property = new \ReflectionProperty(GenerateCommand::class, 'migrationsDir');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getMigrationDirectory');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getSchema()
     */
    public function testGetSchema()
    {
        /* @var $expected Schema|\PHPUnit_Framework_MockObject_MockObject */
        $expected = $this
            ->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $object GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $property = new \ReflectionProperty(GenerateCommand::class, 'schema');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getSchema');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getGenerator()
     */
    public function testGetGenerator()
    {
        /* @var $expected Generator|\PHPUnit_Framework_MockObject_MockObject */
        $expected = $this
            ->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $object GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $property = new \ReflectionProperty(GenerateCommand::class, 'generator');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getGenerator');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getTableSchema()
     */
    public function testGetTableSchema()
    {
        $tableName = $this->getRandomString();

        /* @var $command GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTableName', 'getSchema'])
            ->getMock();

        /* @var $schema Schema|\PHPUnit_Framework_MockObject_MockObject */
        $schema = $this
            ->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTableSchema'])
            ->getMockForAbstractClass();

        /* @var $tableSchema TableSchema|\PHPUnit_Framework_MockObject_MockObject */
        $tableSchema = $this
            ->getMockBuilder(TableSchema::class)
            ->disableOriginalConstructor()
            ->getMock();

        $command->method('getTableName')->willReturn($tableName);
        $command->method('getSchema')->willReturn($schema);
        $schema->method('getTableSchema')->with($tableName)->willReturn($tableSchema);

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getTableSchema');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($command);

        $this->assertSame($tableSchema, $result);
    }

    /**
     * @covers ::getTableName()
     */
    public function testGetTableNameInstanceOfActiveRecord()
    {
        $tableName = $this->getRandomString();
        /* @var $command GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTargetClass'])
            ->getMock();

        $targetClass = new class($tableName) extends ActiveRecord {
            /**
             * @var string
             */
            private static $tableName;

            /**
             * @inheritDoc
             */
            public function __construct(string $tableName)
            {
                self::$tableName = $tableName;

                parent::__construct();
            }

            /**
             * @inheritDoc
             */
            public static function tableName(): string
            {
                return self::$tableName;
            }
        };

        $command->method('getTargetClass')->willReturn(\get_class($targetClass));

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getTableName');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($command);

        $this->assertEquals($tableName, $result);
    }

    /**
     * @covers ::getTableName()
     */
    public function testGetTableNameNotAnActiveRecordInstance()
    {
        /* @var $command GenerateCommand|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this
            ->getMockBuilder(GenerateCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTargetClass'])
            ->getMock();

        $command->method('getTargetClass')->willReturn(self::class);
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage(
            sprintf('You must provide an ActiveRecord subclass. %s given', self::class)
        );

        $reflection = new \ReflectionMethod(GenerateCommand::class, 'getTableName');
        $reflection->setAccessible(true);

        $reflection->invoke($command);
    }

    /**
     * @return string
     */
    private function getRandomString(): string
    {
        return microtime();
    }

    /**
     * @param GenerateCommand $command
     * @param string $property
     * @param mixed $expected
     */
    private function assertPropertyEquals(GenerateCommand $command, string $property, $expected)
    {
        $reflection = new \ReflectionProperty(GenerateCommand::class, $property);
        $reflection->setAccessible(true);

        $this->assertSame($expected, $reflection->getValue($command));
    }
}
