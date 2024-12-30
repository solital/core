<?php

namespace Solital\Core\Console\Tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Console\Tests\Commands\{CustomCommands, Custom};

class ExtendedCommands implements ExtendCommandsInterface
{
    protected array $command_class = [
        CustomCommands::class,
        Custom::class
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
