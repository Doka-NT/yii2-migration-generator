<?php

namespace tests\unit\skobka\yii2\migrationGenerator\factory;

use Exceptions\Operation\UnexpectedException;
use skobka\yii2\migrationGenerator\commands\CommandInterface;
use skobka\yii2\migrationGenerator\commands\GenerateCommand;
use skobka\yii2\migrationGenerator\factory\GeneratorCommandFactory;
use PHPUnit\Framework\TestCase;
use skobka\yii2\migrationGenerator\migration\Generator;
use yii\db\Schema;

/**
 * @coversDefaultClass \skobka\yii2\migrationGenerator\factory\GeneratorCommandFactory
 */
class GeneratorCommandFactoryTest extends TestCase
{
    /**
     * @covers ::__construct()
     */
    public function testConstruct()
    {
        $className = uniqid('className', true);
        $directory = uniqid('directory', true);
        $factory = new GeneratorCommandFactory($className, $directory);

        $this->assertPropertyEquals($factory, 'className', $className);
        $this->assertPropertyEquals($factory, 'directory', $directory);
    }

    /**
     * @covers ::create()
     * @uses \skobka\yii2\migrationGenerator\commands\GenerateCommand::__construct()
     */
    public function testCreate()
    {
        $className = uniqid('className', true);
        $directory = uniqid('directory', true);

        /* @var $factory GeneratorCommandFactory|\PHPUnit_Framework_MockObject_MockObject */
        $factory = $this
            ->getMockBuilder(GeneratorCommandFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createGenerator', 'getSchema', 'getClassName', 'getDirectory'])
            ->getMock();

        /* @var $generator Generator|\PHPUnit_Framework_MockObject_MockObject */
        $generator = $this
            ->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* @var $schema Schema|\PHPUnit_Framework_MockObject_MockObject */
        $schema = $this
            ->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $factory->method('createGenerator')->willReturn($generator);
        $factory->method('getSchema')->willReturn($schema);
        $factory->method('getClassName')->willReturn($className);
        $factory->method('getDirectory')->willReturn($directory);

        $result = $factory->create();

        $this->assertInstanceOf(CommandInterface::class, $result);
        $this->assertInstanceOf(GenerateCommand::class, $result);
    }

    /**
     * @covers ::create()
     * @throws \Exception
     */
    public function testCreateWithException()
    {
        $exceptionMessage = uniqid('message', true);
        $code = random_int(0, 1000);
        /* @var $factory GeneratorCommandFactory|\PHPUnit_Framework_MockObject_MockObject */
        $factory = $this
            ->getMockBuilder(GeneratorCommandFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createGenerator'])
            ->getMock();

        $exception = new \Exception($exceptionMessage, $code);
        $factory->method('createGenerator')->willThrowException($exception);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($code);

        $factory->create();
    }

    /**
     * @covers ::getClassName()
     */
    public function testGetClassName()
    {
        $expected = uniqid('className', true);

        /* @var $object GeneratorCommandFactory|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GeneratorCommandFactory::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $property = new \ReflectionProperty(GeneratorCommandFactory::class, 'className');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GeneratorCommandFactory::class, 'getClassName');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getDirectory()
     */
    public function testGetDirectory()
    {
        $expected = 'directory';

        /* @var $object GeneratorCommandFactory|\PHPUnit_Framework_MockObject_MockObject */
        $object = $this
            ->getMockBuilder(GeneratorCommandFactory::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();


        $property = new \ReflectionProperty(GeneratorCommandFactory::class, 'directory');
        $property->setAccessible(true);
        $property->setValue($object, $expected);

        $reflection = new \ReflectionMethod(GeneratorCommandFactory::class, 'getDirectory');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($object);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param GeneratorCommandFactory $factory
     * @param string $property
     * @param mixed $expected
     */
    private function assertPropertyEquals(GeneratorCommandFactory $factory, string $property, $expected)
    {
        $reflection = new \ReflectionProperty(GeneratorCommandFactory::class, $property);
        $reflection->setAccessible(true);

        $this->assertSame($expected, $reflection->getValue($factory));
    }
}
