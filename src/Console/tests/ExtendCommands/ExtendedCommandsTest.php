<?php

namespace Solital\Core\Console\tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class ExtendedCommandsTest implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [
        \Solital\Core\Console\tests\CustomCommandsTest::class,
        \Solital\Core\Console\tests\CustomTest::class
    ];

    public function getCommandClass(): array
    {
        return $this->command_class;
    }
}
