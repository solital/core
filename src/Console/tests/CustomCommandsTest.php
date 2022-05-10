<?php

namespace Solital\Core\Console\tests;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class CustomCommandsTest extends Command implements CommandInterface
{
    protected string $command = "create:test";
    protected array $arguments = ["name", "email"];
    protected string $description = "Description command";

    public function handle(object $arguments, object $options): mixed
    {
        #var_dump($arguments->name);
        return $options->option;
    }
}
