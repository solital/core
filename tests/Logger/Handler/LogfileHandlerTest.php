<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Handler\LogfileHandler;

class LogfileHandlerTest extends TestCase
{
    private $file;

    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = 'logfileTest';
        $this->obj = new LogfileHandler($this->file);
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
     * @covers Solital\Core\Logger\Handler\LogfileHandler::__construct()
     */
    public function testConstruct()
    {
        $this->assertTrue(file_exists($this->file));
    }

    /**
     * @covers Solital\Core\Logger\Handler\LogfileHandler::doRotation()
     */
    public function testDoRotation()
    {
        $file = 'rotateFile';
        $time = time() - 86400;
        touch($file, $time);
        $r = $this->invokeMethod('doRotation', [$file, LogfileHandler::ROTATE_DATE]);
        $this->assertTrue($r);

        $new = $file . '_' . date('Ymd', $time);
        $this->assertTrue(file_exists($new));
        unlink($new);
    }

    /**
     * @covers Solital\Core\Logger\Handler\LogfileHandler::__invoke()
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