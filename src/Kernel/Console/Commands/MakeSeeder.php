<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Database\Seeds\Seeder;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;

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
     * @var Seeder
     */
    private Seeder $seeder;

    /**
     * Construct
     */
    public function __construct()
    {
        Application::connectionDatabase();
        $this->seeder = new Seeder(Application::DEBUG);
    }

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $this->seeder->create($arguments->seeder_name);

        return true;
    }
}
