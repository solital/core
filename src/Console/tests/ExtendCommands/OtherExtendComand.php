<?php

namespace Solital\Core\Console\Tests\ExtendCommands;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Console\Tests\Commands\{CallSameCommand, MyCommands};

class OtherExtendComand implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [
        MyCommands::class,
        CallSameCommand::class
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
