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
     * @var Seeder
     */
    private Seeder $seeder;

    /**
     * Construct
     */
    public function __construct()
    {
        Application::connectionDatabase();
        $this->seeder = new Seeder();
    }

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $this->seeder->executeSeeds($options);

        return true;
    }
}
