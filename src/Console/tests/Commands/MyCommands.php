<?php

namespace Solital\Core\Console\Tests\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class MyCommands extends Command implements CommandInterface
{
    protected string $command = "user:test";
    //protected array $arguments = ["argument_test"];
    protected string $description = "Description user command";

    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        return $this->getCommand() . " - Command OK" . PHP_EOL;
    }
}
