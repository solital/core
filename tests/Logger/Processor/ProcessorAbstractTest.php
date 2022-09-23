<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Processor;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Processor\ProcessorAbstract;

class myProcessor extends ProcessorAbstract
{
    protected function updateContext(array $context): array
    {
        $context['test'] = 'bingo';
        return $context;
    }
}

class ProcessorAbstractTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new myProcessor();
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
     * @covers Solital\Core\Logger\Processor\ProcessorAbstract::__invoke()
     */
    public function testInvoke()
    {
        $m = new LogEntry('test', ['a' => 'a']);
        $callable = $this->obj;
        $callable($m);

        $this->assertEquals(
            ['a' => 'a', 'test' => 'bingo'],
            $m->getContext()
        );
    }

    /**
     * @covers Solital\Core\Logger\Processor\ProcessorAbstract::updateContext()
     */
    public function testUpdateContext()
    {
        $a = [];
        $b = $this->invokeMethod('updateContext', [$a]);
        $this->assertEquals(
            ['test' => 'bingo'],
            $b
        );
    }
}