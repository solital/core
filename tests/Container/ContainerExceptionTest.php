<?php

namespace Solital\Test\Container;

use PHPUnit\Framework\TestCase;
use Solital\Core\Container\Container;
use Solital\Core\Container\Exception\ContainerException;
use Solital\Core\Container\Exception\ExpectedInvokableException;
use Solital\Core\Container\Exception\ImmutableException;
use Solital\Core\Container\Exception\NotFoundException;

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
     * entry to the container. You must remove it first.
     */
    /* public function testAddResolvedEntry()
    {
        $this->expectException(ImmutableException::class);
        $container = new Container($this->entries);
        $container->add('foo', 'bim');
    } */

    /**
     * Test that the factory method needs to have a valid invokable
     * callback.
     */
    public function testFactoryWithInvalidCallback()
    {
        $this->expectException(ExpectedInvokableException::class);
        $container = new Container();
        $container->factory('foo');
    }

    /**
     * Test that the protect method needs to have a valid invokable
     * callback.
     */
    public function testProtectWithInvalidCallback()
    {
        $this->expectException(ExpectedInvokableException::class);
        $container = new Container();
        $container->protect('foo');
    }

    /**
     * Test that an exception is thrown when getting
     * an entry that does not exist.
     */
    public function testGetNotFoundEntry()
    {
        $this->expectException(NotFoundException::class);
        $container = new Container();
        $container->get('foo');
    }

    /**
     * Test that an exception is thrown when attempting
     * to get an entry that is currently being resolved.
     */
    public function testGetResolvingEntry()
    {
        $this->expectException(ContainerException::class);
        $container = new Container();
        $container->add('foo', function ($container) {
            return $container->get('foo');
        });
        $container->get('foo');
    }

    /**
     * Test that we cannot extend a non-invokable callback.
     */
    public function testGlobalExtendWithNonInvokableCallback()
    {
        $this->expectException(ExpectedInvokableException::class);
        $container = new Container();
        $container->extend(42);
    }

    /**
     * Test that we cannot extend a non-existent entry.
     */
    public function testExtendNonExistantEntry()
    {
        $this->expectException(NotFoundException::class);
        $container = new Container();
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend an entry currently being resolved.
     */
    public function testExtendResolvingEntry()
    {
        $this->expectException(ImmutableException::class);
        $container = new Container();
        $container->add('foo', function () use ($container) {
            $container->extend('foo', function () {
            });
        });
        $container->get('foo');
    }

    /**
     * Test that we cannot extend a parameter definition.
     */
    public function testExtendNonServiceEntry()
    {
        $this->expectException(ExpectedInvokableException::class);
        $container = new Container($this->entries);
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend an already resolved entry.
     */
    public function testExtendResolvedEntry()
    {
        $this->expectException(ImmutableException::class);
        $container = new Container($this->entries);
        $container->get('foo');
        $container->extend('foo', function () {
        });
    }

    /**
     * Test that we cannot extend a protected definition
     * because it is considered a parameter at that point.
     */
    public function testExtendProtectedEntry()
    {
        $this->expectException(ImmutableException::class);
        $container = new Container();
        $container->add('baz', $container->protect(function () {
            return new \StdClass;
        }));
        $container->extend('baz', function () {
        });
    }

    /**
     * Test that we cannot extend with a non-invokable callback.
     */
    public function testExtendWithNonInvokableCallback()
    {
        $this->expectException(ExpectedInvokableException::class);
        $container = new Container($this->entries);
        $container->extend('baz');
    }
}
