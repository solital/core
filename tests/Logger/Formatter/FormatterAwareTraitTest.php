<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Formatter;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\Formatter\DefaultFormatter;
use Solital\Core\Logger\Formatter\FormatterAwareTrait;
use Solital\Core\Logger\Formatter\FormatterAwareInterface;

class myClass implements FormatterAwareInterface
{
    use FormatterAwareTrait;
}

class FormatterAwareTraitTest extends TestCase
{
    private $obj;

    private $ref;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new myClass();
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
     * @covers Solital\Core\Logger\Formatter\FormatterAwareTrait::setFormatter()
     */
    public function testSetFormatter()
    {
        $f = new DefaultFormatter;
        $this->obj->setFormatter($f);
        $this->assertTrue($f === $this->obj->getFormatter());
    }

    /**
     * @covers Solital\Core\Logger\Formatter\FormatterAwareTrait::getFormatter()
     */
    public function testGetFormatter()
    {
        $this->expectExceptionMessage('null');
        $this->obj->getFormatter();
    }
}