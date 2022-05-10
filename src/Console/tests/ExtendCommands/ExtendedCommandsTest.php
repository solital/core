<?php

namespace Solital\Core\Console\tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class ExtendedCommandsTest implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [
        \Solital\Core\Test\CustomCommandsTest::class,
        \Solital\Core\Test\CustomTest::class
    ];

    public function getCommandClass(): array
    {
        return $this->command_class;
    }
}
