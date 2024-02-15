<?php

namespace Solital\Core\Console\tests;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class CustomCommandsTest extends Command implements CommandInterface
{
    protected string $command = "create:test";
    protected array $arguments = [
        "--argument-custom-second"
    ];
    protected string $description = "Description command";

    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        return $options->option;
    }
}
