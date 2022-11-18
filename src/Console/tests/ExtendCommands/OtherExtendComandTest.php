<?php

namespace Solital\Core\Console\tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class OtherExtendComandTest implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [
        \Solital\Core\Console\tests\MyCommandsTest::class
    ];

    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    public function getTypeCommands(): string
    {
        return "Other Extend Commands Test";
    }
}
