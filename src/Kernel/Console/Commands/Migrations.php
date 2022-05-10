<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Database\Migrations\Migration;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;

class Migrations extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "migrate";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Run a migration";

    /**
     * @var Migration
     */
    private Migration $migrations;

    /**
     * Construct
     */
    public function __construct()
    {
        Application::connectionDatabase();
        $this->migrations = new Migration();
    }

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $this->migrations->runMigrationsDB($options);

        return true;
    }
}
