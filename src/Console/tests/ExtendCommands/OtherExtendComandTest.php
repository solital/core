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

    #[\Override]
    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    #[\Override]
    public function getTypeCommands(): string
    {
        return "Other Extend Commands Test";
    }
}
