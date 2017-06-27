<?php

namespace Greg\DependencyInjection;

use PHPUnit\Framework\TestCase;

class IoCContainerTest extends TestCase
{
    public function testCanInitialize()
    {
        $this->assertInstanceOf(IoCContainer::class, new IoCContainer());
    }

    public function testCanInitializeWithPrefixes()
    {
        $container = new IoCContainer($prefixes = ['App\\']);

        $this->assertEquals($prefixes, $container->getPrefixes());
    }

    public function testCanSetPrefixes()
    {
        $container = new IoCContainer();

        $container->setPrefixes($prefixes = ['App\\']);

        $this->assertEquals($prefixes, $container->getPrefixes());
    }

    public function testCanAddPrefixes()
    {
        $container = new IoCContainer();

        $container->addPrefixes('Foo\\');

        $container->addPrefixes('Bar\\', 'Baz\\');

        $this->assertEquals(['Foo\\', 'Bar\\', 'Baz\\'], $container->getPrefixes());
    }

    public function testCanInjectCallable()
    {
        $container = new IoCContainer();

        $container->inject('abstract', function () {
            return new \stdClass();
        });

        $this->assertInstanceOf(\stdClass::class, $container->get('abstract'));
    }

    public function testCanInjectConcrete()
    {
        $container = new IoCContainer();

        $container->inject('abstract', 'stdClass');

        $this->assertInstanceOf(\stdClass::class, $container->get('abstract'));
    }

    public function testCanInjectObject()
    {
        $container = new IoCContainer();

        $container->inject('abstract', new \stdClass());

        $this->assertInstanceOf(\stdClass::class, $container->get('abstract'));
    }

    public function testCanInjectWithArguments()
    {
        $container = new IoCContainer();

        $container->inject('abstract', function ($foo) {
            $this->assertEquals('foo', $foo);

            return new \stdClass();
        }, 'foo');

        $this->assertInstanceOf(\stdClass::class, $container->get('abstract'));
    }

    public function testCanThrowExceptionIfAbstractIsAlreadyDefined()
    {
        $container = new IoCContainer();

        $container->inject('abstract', \stdClass::class);

        $this->expectException(\Exception::class);

        $container->inject('abstract', \stdClass::class);
    }

    public function testCanThrowExceptionIfRegisteredObjectIsAlreadyDefined()
    {
        $container = new IoCContainer();

        $class = new \stdClass();

        $container->register($class);

        $this->expectException(\Exception::class);

        $container->register($class);
    }

    public function testCanThrowExceptionIfWrongConcrete()
    {
        $container = new IoCContainer();

        $this->expectException(\Exception::class);

        $container->inject('abstract', ['foo']);
    }

    public function testCanRegister()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $this->assertInstanceOf(\stdClass::class, $container->get(\stdClass::class));
    }

    public function testCanThrowExceptionIfRegisteredIsNotAnObject()
    {
        $container = new IoCContainer();

        $this->expectException(\Exception::class);

        $container->register(\stdClass::class);
    }

    public function testCanGetNull()
    {
        $container = new IoCContainer();

        $this->assertNull($container->get('undefined'));
    }

    public function testCanExpectAConcrete()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $this->assertInstanceOf(\stdClass::class, $container->expect(\stdClass::class));
    }

    public function testCanThrowExceptionIfConcreteNotFound()
    {
        $container = new IoCContainer();

        $this->expectException(\Exception::class);

        $container->expect(\stdClass::class);
    }

    public function testCanGetPrefixedObjects()
    {
        $container = new IoCContainer(['std']);

        $this->assertInstanceOf(\stdClass::class, $container->get(\stdClass::class));
    }

    public function testCanLoadClass()
    {
        $container = new IoCContainer();

        $this->assertInstanceOf(\stdClass::class, $container->load(\stdClass::class));
    }

    public function testCanLoadClassWithArguments()
    {
        $container = new IoCContainer();

        $this->assertInstanceOf(TestClassWithArguments::class, $class = $container->loadArgs(TestClassWithArguments::class, ['foo']));

        $this->assertEquals('foo', $class->foo);
    }

    public function testCanLoadInternalClassWithArguments()
    {
        $container = new IoCContainer();

        $this->assertInstanceOf(\ArrayObject::class, $class = $container->loadArgs(\ArrayObject::class, [['foo' => 'FOO']]));

        $this->assertEquals('FOO', $class['foo']);
    }

    public function testCanCall()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $success = false;

        $container->call(function (\stdClass $class) use (&$success) {
            $success = true;

            return $class;
        });

        $this->assertTrue($success);
    }

    public function testCanCallWithArgumentsReferences()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $success = false;

        $container->call(function (&$success, \stdClass $class) {
            $success = true;

            return $class;
        }, $success);

        $this->assertTrue($success);
    }

    public function testCanCallWithArguments()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $success = false;

        $container->callArgs(function ($arg, \stdClass $class) use (&$success) {
            $this->assertEquals('foo', $arg);

            $success = true;

            return $class;
        }, ['foo']);

        $this->assertTrue($success);
    }

    public function testCanCallWithMixedArgumentsReferences()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $success = false;

        $container->call(function (\stdClass $class, &$success) {
            $success = true;

            return $class;
        }, $success);

        $this->assertTrue($success);
    }

    public function testCanCallWithMixedArguments()
    {
        $container = new IoCContainer();

        $container->register(new \stdClass());

        $success = false;

        $container->callArgs(function (\stdClass $class, $arg) use (&$success) {
            $this->assertEquals('foo', $arg);

            $success = true;

            return $class;
        }, ['foo']);

        $this->assertTrue($success);
    }

    public function testCanCallWithOptionalMixedArgument()
    {
        $container = new IoCContainer();

        $success = false;

        $container->call(function ($arg = null) use (&$success) {
            $success = true;

            $this->assertNull($arg);
        });

        $this->assertTrue($success);
    }

    public function testCanCallWithOptionalTypeArgument()
    {
        $container = new IoCContainer();

        $success = false;

        $container->call(function (\stdClass $arg = null) use (&$success) {
            $success = true;

            $this->assertNull($arg);
        });

        $this->assertTrue($success);
    }

    public function testCanCallWithVariadicParameter()
    {
        $container = new IoCContainer();

        $success = false;

        $container->callArgs(function (...$arguments) use (&$success) {
            $this->assertEquals(['foo', 'bar'], $arguments);

            $success = true;
        }, ['foo', 'bar']);

        $this->assertTrue($success);
    }

    public function testCanCallWithMixedObjects()
    {
        $container = new IoCContainer();

        $success = false;

        $container->callArgs(function (\stdClass $arg1, $arg2) use (&$success) {
            $success = true;

            $this->assertEquals('foo', $arg2);

            return $arg1;
        }, ['foo', new \stdClass()]);

        $this->assertTrue($success);
    }

    public function testCanCallWithMoreOptionalMixedParametersThenArguments()
    {
        $container = new IoCContainer();

        $success = false;

        $container->callArgs(function (string $arg1 = null, \stdClass $arg2 = null, string $arg3 = null, \stdClass $arg4 = null) use (&$success) {
            $success = true;

            $this->assertNull($arg1);

            $this->assertNotNull($arg2);

            $this->assertNull($arg3);

            $this->assertNotNull($arg4);
        }, [null, new \stdClass()]);

        $this->assertTrue($success);
    }

    public function testCanReturnValueReference()
    {
        $container = new IoCContainer();

        $foo = 'foo';

        $bar = &$container->callArgs(function &() use (&$foo) {
            return $foo;
        }, []);

        $bar = 'bar';

        $this->assertEquals('bar', $foo);
    }
}

class TestClassWithArguments
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}
