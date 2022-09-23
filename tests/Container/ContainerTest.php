<?php

namespace Solital\Core\Container\tests;

use PHPUnit\Framework\TestCase;
use Solital\Core\Container\Container;

/**
 * Container testing.
 */
class ContainerTest extends TestCase
{
	private array $entries = [
		'foo' => 'bar',
		'foo2' => 'baz'
	];

	/**
	 * Provides an invokable class.
	 */
	public function invokableProvider()
	{
		return [
			[new class
			{
				public function __invoke()
				{
					return new \StdClass();
				}
			}]
		];
	}

	/**
	 * Test that a container can be instantiated
	 * with an array of entries.
	 * 
	 */
	public function testInstantiateWithEntries()
	{
		$container = new Container($this->entries);

		$this->assertCount(2, $container->keys());
	}

	/**
	 * Test that the container can check whether
	 * or not it has entries.
	 * 
	 */
	public function testHas()
	{
		$container = new Container($this->entries);
		$this->assertTrue($container->has('foo'));
	}

	/**
	 * Test the ability to add entries to the container.
	 * 
	 */
	public function testAdd()
	{
		$container = new Container();

		foreach ($this->entries as $id => $value) {
			$container->add($id, $value);
		}

		$this->assertTrue($container->has('foo'));
	}

	/**
	 * Test that you can add a null value to the container
	 * to be interpreted as a parameter.
	 */
	public function testAddNullValue()
	{
		$container = new Container();
		$container->add('foo', null);

		$this->assertEquals(null, $container->get('foo'));
	}

	/**
	 * Test that you can get all entry names.
	 * 
	 */
	public function testGetAllEntryKeys()
	{
		$container = new Container($this->entries);

		$this->assertEquals(['foo', 'foo2'], $container->keys());
	}

	/**
	 * Test that you can register a service provider
	 * and get back the container.
	 */
	public function testRegisterServiceProvider()
	{
		$container = new Container();
		$container = $container->register(new ServiceTest);

		$this->assertInstanceOf(Container::class, $container);
	}

	/**
	 * Test that can get services and parameters from the container
	 * correctly.
	 * 
	 */
	public function testGetParameterAndServiceEntries()
	{
		$container = new Container($this->entries);

		$this->assertEquals('bar', $container->get('foo'));
	}

	/**
	 * Test that we can get a protected entry in the container
	 * that is interpreted as a literal value.
	 * 
	 */
	public function testGetProtectedEntry()
	{
		$container = new Container();
		$container->add('baz', $container->protect(function () {
			return new \StdClass;
		}));

		$callback = function () {
			return new \StdClass();
		};

		$this->assertEquals($callback, $container->get('baz'));
	}

	/**
	 * Test that we can get an entry from the container that
	 * is marked as a factory.
	 * 
	 */
	public function testGetFactoryService()
	{
		$container = new Container();
		$container->add('baz', $container->factory(function () {
			return new \StdClass;
		}));

		$this->assertInstanceOf(\StdClass::class, $container->get('baz'));
	}

	/**
	 * Test that, by default, the container will "share" service
	 * instances.
	 * 
	 */
	public function testGetSharesInstanceByDefault()
	{
		$container = new Container($this->entries);

		$container['user'] = function () {
			return new \StdClass();
		};

		$instance = $container->get('user');
		$this->assertInstanceOf(\StdClass::class, $instance);
	}

	/**
	 * Test that, if a service is marked as a factory, the container
	 * will return a new instance of the service every time it is
	 * resolved.
	 * 
	 */
	public function testGetFactoryReturnsNewInstance()
	{
		$container = new Container($this->entries);
		$container->add('baz', $container->factory(function () {
			return new \StdClass;
		}));

		$obj1 = $container->get('baz');
		$this->assertInstanceOf(\StdClass::class, $obj1);

		$obj2 = $container->get('baz');
		$this->assertInstanceOf(\StdClass::class, $obj2);

		$this->assertNotSame($obj1, $obj2);
	}

	/**
	 * Test that we can get a service via an invokable class.
	 * 
	 * @dataProvider invokableProvider
	 */
	public function testGetViaInvokeMethod($invokable)
	{
		$container = new Container();
		$container->add('foo', $invokable);

		$this->assertInstanceOf(\StdClass::class, $container->get('foo'));
	}

	/**
	 * Test that we can remove entries.
	 * 
	 */
	public function testRemoveServiceEntry()
	{
		$container = new Container($this->entries);

		$this->assertIsString('bar', $container->get('foo'));

		$container->remove('foo');

		$this->assertFalse($container->has('foo'));
	}

	/**
	 * Test that can can remove services marked as factories.
	 * 
	 */
	public function testRemoveServiceFactoryEntry()
	{
		$container = new Container();

		$container->add('baz', $container->factory(function () {
			return new \StdClass();
		}));

		$this->assertTrue($container->has('baz'));

		$container->remove('baz');

		$this->assertFalse($container->has('baz'));
	}

	/**
	 * Test that we can also remove protected entries.
	 * 
	 */
	public function testRemoveProtectedEntry()
	{
		$container = new Container();
		$container->add('baz', $container->protect(function () {
			return new \StdClass();
		}));

		$this->assertTrue($container->has('baz'));

		$container->remove('baz');

		$this->assertFalse($container->has('baz'));
	}

	/**
	 * Test that callbacks marked as global get called correctly
	 * on every resolve.
	 * 
	 */
	/* public function testCallGlobalExtendCallbackOnEveryResolve()
	{
		$container = new Container($this->entries);
		$container->counter = 1;
		$container->extend(function($resolved, $container) {
			if (is_object($resolved)) {
				$resolved->container = $container;
			}
			
			$container->counter++;
		});

		$foo = $container->get('foo');

		$this->assertInstanceOf(Container::class, $foo->container);
	} */

	/**
	 * Test that when we extend a service that is marked as a factory,
	 * that the factory array updates.
	 * 
	 */
	public function testExtendFactoryUpdatesFactoryDefinition()
	{
		$container = new Container();
		$container->add('foo3', $container->factory(function () {
			return new \StdClass;
		}));

		$container->extend('foo3', function ($obj) {
			$obj->value = 42;
			return $obj;
		});

		$obj = $container->get('foo3');

		$this->assertEquals(42, $obj->value);
	}

	/**
	 * Test that when extending a definition, the callback is passed 
	 * the object instance and the container instance.
	 * 
	 */
	public function testExtendPassesObjectInstanceAndContainer()
	{
		$container = new Container();
		$container->add('baz', $container->factory(function () {
			return new \StdClass;
		}));

		$container->extend('baz', function ($obj, $container) {
			return [$obj, $container];
		});

		$array = $container->get('baz');

		$this->assertInstanceOf(\StdClass::class, $array[0]);
		$this->assertInstanceOf(Container::class, $array[1]);
	}

	/**
	 * Test that we can access the container entries like an array.
	 */
	public function testArrayAccess()
	{
		$container = new Container();

		$container['foo'] = 'bar';
		$this->assertTrue(isset($container['foo']));

		$foo = $container['foo'];
		$this->assertEquals('bar', $foo);

		unset($container['foo']);
		$this->assertFalse(isset($container['foo']));
	}
}
