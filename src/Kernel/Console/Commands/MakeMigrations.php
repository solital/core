<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Database\Migrations\Migration;
use Solital\Core\Console\Interface\CommandInterface;

class MakeMigrations extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:migration";

    /**
     * @var array
     */
    protected array $arguments = ["migration_name"];

    /**
     * @var string
     */
    protected string $description = "Create a migration";

    /**
     * @var mixed
     */
    private mixed $targetVersion = false;

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
        try {
            if (empty($arguments->migration_name) || !isset($arguments->migration_name)) {
                $arguments->migration_name = "";
            }

            $this->migrations->createMigration($arguments->migration_name);

            return true;
        } catch (\Exception $e) {
            $this->error("Error running migration: " . $e->getMessage())->print()->break();

            return false;
        }

        return $this;
    }
}