<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Database\Seeds\Seeder;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;

class MakeSeeder extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:seeder";

    /**
     * @var array
     */
    protected array $arguments = ["seeder_name"];

    /**
     * @var string
     */
    protected string $description = "Create a Seed class";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        if (!isset($arguments->seeder_name)) {
            ConsoleOutput::error("Error: Seeder name not found")->print()->break();
            return false;
        }

        Application::connectionDatabase();
        (new Seeder())->create($arguments->seeder_name);

        return true;
    }
}
