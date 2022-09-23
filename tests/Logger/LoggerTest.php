<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests;

use Psr\Log\LogLevel;
use Solital\Core\Logger\Logger;
use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Entry\MemoryInfo;
use Solital\Core\Logger\Handler\EchoHandler;
use Solital\Core\Logger\Entry\LogEntryInterface;

class MyEntry extends LogEntry
{
}

class LoggerTest extends TestCase
{
    private $obj;
    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new Logger('LOG');
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
     * @covers Solital\Core\Logger\Logger::log()
     */
    public function testLog()
    {
        $echoHandler = new EchoHandler();

        $this->obj->addHandler(LogLevel::INFO, $echoHandler);
        $this->expectOutputRegex('/test.*peak.*/s');

        $this->obj->info('test');

        $this->obj->addHandler(LogLevel::ALERT, $echoHandler, MemoryInfo::class);
        $this->obj->alert(new MemoryInfo());
    }

    /**
     * unknown log level
     *
     * @covers Solital\Core\Logger\Logger::log()
     */
    public function testLog2()
    {
        $this->expectExceptionMessage('Unknown log level');
        $this->obj->log('test', 'test');
    }

    /**
     * closure as handler
     *
     * @covers Solital\Core\Logger\Logger::log()
     */
    public function testLog3()
    {
        // closure as handler
        $handler = function(LogEntryInterface $entry) {
            echo (string) $entry;
        };

        // set class processor
        $this->obj->addHandler(
            'warning',
            $handler,
            MyEntry::addProcessor(
                function(LogEntryInterface $entry) {
                    $context = $entry->getContext();
                    $context['wow'] = 'bingo';
                    $entry->setContext($context);
                }
            )
        );

        $this->expectOutputString('my is bingo');
        $this->obj->info('info is {wow}');
        $this->obj->error('error is {wow}');
        $this->obj->error(new MyEntry('my is {wow}'));
    }
}