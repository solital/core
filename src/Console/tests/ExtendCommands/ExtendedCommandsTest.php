<?php

namespace Solital\Core\Console\tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class ExtendedCommandsTest implements ExtendCommandsInterface
{
    protected array $command_class = [
        \Solital\Core\Console\tests\CustomCommandsTest::class,
        \Solital\Core\Console\tests\CustomTest::class,
        \Solital\Core\Console\tests\NotFoundClass::class,
        \Solital\Core\Console\tests\SecondNotFoundClass::class
    ];

    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    public function getTypeCommands(): string
    {
        return "Extended Commands Test";
    }
}
