<?php

namespace Solital\Console;

use Solital\Core\Console\Interface\ExtendCommandsInterface;

class Config implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [];

    /**
     * @var string
     */
    protected string $type_commands = "";

    /**
     * @return array
     */
    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    /**
     * @return string
     */
    public function getTypeCommands(): string
    {
        return $this->type_commands;
    }
}
