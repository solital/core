<?php

namespace Solital\Core\Kernel\Console;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Kernel\Console\Commands\{
    DumpDatabase,
    GenerateConfigFiles,
    HandleCache,
    HandleCourse,
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
    Version
};

class SolitalCommands implements ExtendCommandsInterface
{
    /**
     * @var array
     */
    protected array $command_class = [
        DumpDatabase::class,
        GenerateConfigFiles::class,
        HandleCache::class,
        HandleCourse::class,
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
        Version::class
    ];

    /**
     * @return array
     */
    public function getCommandClass(): array
    {
        return $this->command_class;
    }
}
