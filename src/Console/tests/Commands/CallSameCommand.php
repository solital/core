<?php

namespace Solital\Core\Console\Tests\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class CallSameCommand extends Command implements CommandInterface
{
    protected string $command = "same:command";

    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        Command::call('same:command');
        return $this;
    }
}