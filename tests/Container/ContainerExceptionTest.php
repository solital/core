<?php

namespace Solital\Core\Container\tests;

use PHPUnit\Framework\TestCase;
use Solital\Core\Container\Container;

/**
 * Container testing.
 */
class ContainerExceptionTest extends TestCase
{
    private array $entries = [
        'foo' => 'bar',
        'foo2' => 'baz'
    ];

    /**
     * Test that you cannot add an already resolved
     * entry to the container. You must remove it
     * first.
     * 
     * @expectedException Solital\Core\Container\Exception\ImmutableException
     */
    /* public function testAddResolvedEntry()
    {
        $container = new Container($this->entries);

        $this->expectException(\Solital\Core\Container\Exception\ImmutableException::class);
        $container->add('foo', 'bim');
    } */

    /**
     * Test that the factory method needs to have a valid invokable
     * callback.
     * 
     * @expectedException Solital\Core\Container\Exception\ExpectedInvokableException
     */
    public function testFactoryWithInvalidCallback()
    {
        $container = new Container();

        $this->expectException(\Solital\Core\Container\Exception\ExpectedInvokableException::class);
        $container->factory('foo');
    }

    /**
     * Test that the protect method needs to have a valid invokable
     * callback.
     * 
     * @expectedException Solital\Core\Container\Exception\ExpectedInvokableException
     */
    public function testProtectWithInvalidCallback()
    {
        $container = new Container();

        $this->expectException(\Solital\Core\Container\Exception\ExpectedInvokableException::class);
        $container->protect('foo');
    }

    /**
     * Test that an exception is thrown when getting
     * an entry that does not exist.
     * 
     * @expectedException Solital\Core\Container\Exception\NotFoundException
     */
    public function testGetNotFoundEntry()
    {
        $container = new Container();

        $this->expectException(\Solital\Core\Container\Exception\NotFoundException::class);
        $container->get('foo');
    }

    /**
     * Test that an exception is thrown when attempting
     * to get an entry that is currently being resolved.
     * 
     * @expectedException Solital\Core\Container\Exception\ContainerException
     */
    public function testGetResolvingEntry()
    {
        $container = new Container();
        $container->add('foo', function ($container) {
            return $container->get('foo');
        });

        $this->expectException(\Solital\Core\Container\Exception\ContainerException::class);
        $container->get('foo');
    }

    /**
     * Test that we cannot extend a non-invokable callback.
     * 
     * @expectedException Solital\Core\Container\Exception\ExpectedInvokableException
     */
    public function testGlobalExtendWithNonInvokableCallback()
    {
        $container = new Container();

        $this->expectException(\Solital\Core\Container\Exception\ExpectedInvokableException::class);
        $container->extend(42);
    }

    /**
     * Test that we cannot extend a non-existent entry.
     * 
     * @expectedException Solital\Core\Container\Exception\NotFoundException
     */
    public function testExtendNonExistantEntry()
    {
        $container = new Container();

        $this->expectException(\Solital\Core\Container\Exception\NotFoundException::class);
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend an entry currently being resolved.
     * 
     * @expectedException Solital\Core\Container\Exception\ImmutableException
     */
    public function testExtendResolvingEntry()
    {
        $container = new Container();
        $container->add('foo', function () use ($container) {
            $container->extend('foo', function () {
            });
        });

        $this->expectException(\Solital\Core\Container\Exception\ImmutableException::class);
        $container->get('foo');
    }

    /**
     * Test that we cannot extend a parameter definition.
     * 
     * @expectedException Solital\Core\Container\Exception\ExpectedInvokableException
     */
    public function testExtendNonServiceEntry()
    {
        $container = new Container($this->entries);

        $this->expectException(\Solital\Core\Container\Exception\ExpectedInvokableException::class);
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend an already resolved entry.
     * 
     * @expectedException Solital\Core\Container\Exception\ImmutableException
     */
    public function testExtendResolvedEntry()
    {
        $container = new Container($this->entries);
        $container->get('foo');

        $this->expectException(\Solital\Core\Container\Exception\ImmutableException::class);
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend a protected definition
     * because it is considered a parameter at that point.
     * 
     * @expectedException Solital\Core\Container\Exception\ImmutableException
     */
    public function testExtendProtectedEntry()
    {
        $container = new Container();
        $container->add('baz', $container->protect(function () {
            return new \StdClass;
        }));

        $this->expectException(\Solital\Core\Container\Exception\ImmutableException::class);
        $container->extend('baz', function () {
        });
    }

    /**
     * Test that we cannot extend with a non-invokable callback.
     * 
     * @expectedException Solital\Core\Container\Exception\ExpectedInvokableException
     */
    public function testExtendWithNonInvokableCallback()
    {
        $container = new Container($this->entries);

        $this->expectException(\Solital\Core\Container\Exception\ExpectedInvokableException::class);
        $container->extend('baz');
    }
}
