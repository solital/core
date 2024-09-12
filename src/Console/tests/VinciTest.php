<?php

namespace Solital\Core\Console\Tests;

use PHPUnit\Framework\TestCase;
use Solital\Core\Console\{Command, CommandException};
use Solital\Core\Console\Tests\ExtendCommands\{ExtendedCommandsTest, OtherExtendComandTest};

class VinciTest extends TestCase
{
    private array $class_commands = [
        ExtendedCommandsTest::class,
        OtherExtendComandTest::class
    ];

    public function testReadWithoutArgument()
    {
        $res = (new Command($this->class_commands))->read('user:test');
        $this->assertEquals("user:test - Command OK\r\n", $res);
    }

    public function testReadWithOptionEmpty()
    {
        $res = (new Command($this->class_commands))->read('create:test', ['', '', '--option']);
        $this->assertEquals(true, $res);
    }

    public function testReadWithOptionValue()
    {
        $res = (new Command($this->class_commands))->read('create:test', ['', '', '--witharg=accept']);
        $this->assertEquals("accept", $res);
    }

    public function testReadArgument()
    {
        $res = (new Command($this->class_commands))->read('create:testsecond', ['', '', 'Vinci']);
        $this->assertEquals("Vinci", $res);
    }

    public function testExceptionSameCommand()
    {
        $this->expectException(CommandException::class);
        (new Command($this->class_commands))->read('same:command');
    }
}
