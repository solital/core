<?php

namespace Solital\Core\Console\tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class ExtendedCommandsTest implements ExtendCommandsInterface
{
    protected array $command_class = [
        \Solital\Core\Console\tests\CustomCommandsTest::class,
        \Solital\Core\Console\tests\CustomTest::class
    ];

    #[\Override]
    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    #[\Override]
    public function getTypeCommands(): string
    {
        return "Extended Commands Test";
    }
}
