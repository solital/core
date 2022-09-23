<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Formatter;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Formatter\AnsiFormatter;

class AnsiFormatterTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new AnsiFormatter();
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
     * @covers Solital\Core\Logger\Formatter\AnsiFormatter::format()
     */
    public function testFormatter()
    {
        $m = new LogEntry('test {wow}', ['__channel' => 'PHOOLE', 'wow' => 'bingo']);
        $m->setLevel('error');
        $this->expectOutputRegex('/test bingo/');
        echo $this->obj->format($m);
    }
}