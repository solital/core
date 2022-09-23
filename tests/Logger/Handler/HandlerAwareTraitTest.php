<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Entry\MemoryInfo;
use Solital\Core\Logger\Handler\LogfileHandler;
use Solital\Core\Logger\Handler\HandlerAwareTrait;
use Solital\Core\Logger\Handler\HandlerAwareInterface;

class myHandlerAware implements HandlerAwareInterface
{
    use HandlerAwareTrait;
}

class HandlerAwareTraitTest extends TestCase
{
    private $file;

    private $file2;

    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = 'handlerAware';
        $this->file2 = 'handlerAware2';
        $this->obj = new myHandlerAware();
        $this->ref = new \ReflectionClass(get_class($this->obj));
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->file)) {
            unlink($this->file);
        }
        if (\file_exists($this->file2)) {
            unlink($this->file2);
        }
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
     * @covers Solital\Core\Logger\Handler\HandlerAwareTrait::addHandler()
     */
    public function testAddHandler()
    {
        $filelog = new LogfileHandler($this->file);
        $this->obj->addHandler(LogLevel::ERROR, $filelog);

        $m = (new LogEntry())->setLevel(LogLevel::INFO);
        $h = $this->invokeMethod('getHandlers', [$m]);
        $this->assertEquals(0, count($h));

        $m = (new LogEntry())->setLevel(LogLevel::ERROR);
        $h = $this->invokeMethod('getHandlers', [$m]);
        $this->assertEquals(1, count($h));
    }

    /**
     * @covers Solital\Core\Logger\Handler\HandlerAwareTrait::getHandlers()
     */
    public function testGetHandlers()
    {
        $file = new LogfileHandler($this->file);
        $file2 = new LogfileHandler($this->file2);

        $this->obj->addHandler(LogLevel::INFO, $file);

        $this->obj->addHandler(
            LogLevel::ERROR,
            $file2,
            MemoryInfo::class
        );

        $m = (new LogEntry())->setLevel(LogLevel::ALERT);
        $h = $this->invokeMethod('getHandlers', [$m]);
        $this->assertEquals(1, count($h));

        $m = (new MemoryInfo())->setLevel(LogLevel::ALERT);
        $h = $this->invokeMethod('getHandlers', [$m]);
        $this->assertEquals(2, count($h));
    }
}