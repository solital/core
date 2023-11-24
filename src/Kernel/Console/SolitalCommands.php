<?php

namespace Solital\Core\Kernel\Console;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Kernel\Console\Commands\{
    DumpDatabase,
    GenerateConfigFiles,
    HandleCache,
    HandleCourse,
    ListDatabase,
    MakeController,
    MakeAuth,
    MakeCommand,
    MakeMiddleware,
    MakeMigrations,
    MakeModel,
    MakeQueue,
    MakeRouter,
    MakeSeeder,
    Migrations,
    Queues,
    Seeders,
    Server,
    Version
};

class SolitalCommands implements ExtendCommandsInterface
{
    /**
     * @var string
     */
    protected string $type_commands = "Solital Commands";

    /**
     * @var array
     */
    protected array $command_class = [
        DumpDatabase::class,
        GenerateConfigFiles::class,
        HandleCache::class,
        HandleCourse::class,
        ListDatabase::class,
        MakeController::class,
        MakeAuth::class,
        MakeCommand::class,
        MakeMiddleware::class,
        MakeMigrations::class,
        MakeModel::class,
        MakeQueue::class,
        MakeRouter::class,
        MakeSeeder::class,
        Migrations::class,
        Queues::class,
        Seeders::class,
        Server::class,
        Version::class
    ];

    /**
     * @return array
     */
    public function getCommandClass(): array
    {
        return $this->command_class;
    }

    /**
     * @return string
     */
    public function getTypeCommands(): string
    {
        return $this->type_commands;
    }
}
