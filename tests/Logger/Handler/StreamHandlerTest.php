<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Handler\StreamHandler;

class StreamHandlerTest extends TestCase
{
    private $file;

    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = 'streamTest';
        $this->obj = new StreamHandler($this->file);
        $this->ref = new \ReflectionClass(get_class($this->obj));
    }

    /* protected function tearDown(): void
    {
        unlink($this->file);
        $this->obj = $this->ref = NULL;
        parent::tearDown();
    } */

    protected function invokeMethod($methodName, array $parameters = array())
    {
        $method = $this->ref->getMethod($methodName);
        $method->setAccessible(TRUE);
        return $method->invokeArgs($this->obj, $parameters);
    }

    /**
     * @covers Solital\Core\Logger\Handler\StreamHandler::__construct()
     */
    public function testConstruct()
    {
        $this->assertTrue(file_exists($this->file));
    }

    /**
     * @covers Solital\Core\Logger\Handler\StreamHandler::__invoke()
     */
    public function testInvoke()
    {
        $m = new LogEntry('test');
        $handler = $this->obj;
        $handler($m);
        $this->assertEquals(
            'INFO: test',
            trim(file_get_contents($this->file))
        );
    }
}