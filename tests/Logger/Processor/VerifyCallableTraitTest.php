<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Processor;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;

class VerifyCallableTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function invokeMethod(object $object, $methodName, array $parameters = array())
    {
        $ref = new \ReflectionClass(get_class($object));
        $method = $ref->getMethod($methodName);
        $method->setAccessible(TRUE);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers Solital\Core\Logger\Processor\VerifyCallableTrait::verifyCallable()
     */
    public function testVerifyCallable()
    {
        $obj = new LogEntry();
        $callable = function(LogEntry $entry) { };
        $this->invokeMethod($obj, 'verifyCallable', [$callable, LogEntry::class]);

        $this->expectExceptionMessage('non valid processor');
        $callable = function() { };
        $this->invokeMethod($obj, 'verifyCallable', [$callable, LogEntry::class]);
    }
}