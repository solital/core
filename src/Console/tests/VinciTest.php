<?php

namespace Solital\Core\Console\Tests;

use PHPUnit\Framework\TestCase;
use Solital\Core\Console\{Command, CommandException};
use Solital\Core\Console\Tests\ExtendCommands\{ExtendedCommands, OtherExtendComand};

class VinciTest extends TestCase
{
    private Command $command;

    private array $class_commands = [
        ExtendedCommands::class,
        OtherExtendComand::class
    ];

    public function setUp() : void
    {
        $this->command = new Command($this->class_commands);
    }

    public function testReadWithoutArgument()
    {
        $res = $this->command->read('user:test');
        $this->assertEquals("user:test - Command OK\r\n", $res);
    }

    public function testReadWithOptionEmpty()
    {
        $res = $this->command->read('create:test', ['', '', '--option']);
        $this->assertEquals(true, $res);
    }

    public function testReadWithOptionValue()
    {
        $res = $this->command->read('create:test', ['', '', '--witharg=accept']);
        $this->assertEquals("accept", $res);
    }

    public function testReadArgument()
    {
        $res = $this->command->read('create:testsecond', ['', '', 'Vinci']);
        $this->assertEquals("Vinci", $res);
    }

    public function testExceptionSameCommand()
    {
        $this->expectException(CommandException::class);
        $this->command->read('same:command');
    }
}
