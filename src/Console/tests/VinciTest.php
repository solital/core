<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Console\Command;
use Solital\Core\Console\tests\ExtendCommands\{ExtendedCommandsTest, OtherExtendComandTest};

class VinciTest extends TestCase
{
    private array $class_commands = [
        ExtendedCommandsTest::class,
        OtherExtendComandTest::class
    ];

    public function testReadWithoutOption()
    {
        $res = (new Command($this->class_commands))->read('user:test');
        $this->assertEquals("user:test", $res);
    }

    public function testReadWithOptionEmpty()
    {
        $res = (new Command($this->class_commands))->read('create:test', ['--option']);
        $this->assertEquals(true, $res);
    }

    public function testReadWithOptionValue()
    {
        $res = (new Command($this->class_commands))->read('create:test', ['--option=accept']);
        $this->assertEquals("accept", $res);
    }
}
