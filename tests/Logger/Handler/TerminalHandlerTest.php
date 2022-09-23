<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Handler\TerminalHandler;

class TerminalHandlerTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new TerminalHandler();
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
     * @covers Solital\Core\Logger\Handler\TerminalHandler::__construct()
     */
    public function testConstruct()
    {
        $obj1 = new TerminalHandler('php://stdout');
        $this->expectExceptionMessage('unknown stream');
        $obj2 = new TerminalHandler('test');
    }
}