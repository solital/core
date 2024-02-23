<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Database\Seeds\Seeder;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;

class Seeders extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "seeder";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Run a user-created Seeder";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        Application::connectionDatabase();
        $seeder = new Seeder();
        $seeder->executeSeeds($options);

        return $this;
    }
}
