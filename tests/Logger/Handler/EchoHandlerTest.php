<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Handler\EchoHandler;

class EchoHandlerTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new EchoHandler();
        $this->ref = new \ReflectionClass(get_class($this->obj));
    }

    protected function tearDown(): void
    {
        $this->obj = $this->ref = NULL;
        parent::tearDown();
    }

    protected function invokeMethod($methodName, array $parameters = array())
    {
        $method = $this->ref->getMethod($methodName);
        $method->setAccessible(TRUE);
        return $method->invokeArgs($this->obj, $parameters);
    }

    /**
     * @covers Solital\Core\Logger\Handler\EchoHandler::__invoke()
     */
    public function testInvoke()
    {
        $m = new LogEntry('test');
        $this->expectOutputRegex('/test/');
        $handler = $this->obj;
        $handler($m);
    }
}