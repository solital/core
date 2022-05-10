<?php

namespace Solital\Core\Console\tests;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class CustomTest extends Command implements CommandInterface
{
    protected string $command = "create:testesecond";
    protected array $arguments = [
        "--argument-custom-second"
    ];
    protected string $description = "Description second command";

    public function handle(object $arguments, object $options): mixed
    {
        #$res = $this->getArguments();
        var_dump($this->getCommand());
    }
}
