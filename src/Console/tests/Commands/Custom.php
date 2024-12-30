<?php

namespace Solital\Core\Console\Tests\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;

class Custom extends Command implements CommandInterface
{
    protected string $command = "create:testsecond";
    protected array $arguments = ["name"];
    protected string $description = "Description second command";

    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        if (isset($options->nondefinedopt)) {
            var_dump(true);
        }

        print_r(Command::call('create:test', ['--witharg=accept']));
        echo PHP_EOL;
        
        return $arguments->name;
    }
}
