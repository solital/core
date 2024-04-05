<?php

namespace Solital\Core\Kernel\Console;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Console\Output\{ColorsEnum, ConsoleOutput};
use Solital\Core\Kernel\{Application, DebugCore};
use Solital\Core\Kernel\Console\Commands\{
    AppStatus,
    DumpDatabase,
    GenerateConfigFiles,
    GenerateHash,
    HandleCache,
    HandleCourse,
    ListDatabase,
    MakeController,
    MakeAuth,
    MakeBoot,
    MakeCommand,
    MakeMiddleware,
    MakeMigrations,
    MakeModel,
    MakeQueue,
    MakeRouter,
    MakeSchedule,
    MakeSeeder,
    MakeValidator,
    Migrations,
    Queues,
    Scanner,
    Schedule,
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

    public function __construct()
    {
        if (DebugCore::isCoreDebugEnabled()) {
            ConsoleOutput::banner("DEBUG ENABLED", ColorsEnum::BG_YELLOW)->print()->break();
        }

        Application::getInstance();
    }

    /**
     * @var array
     */
    protected array $command_class = [
        AppStatus::class,
        DumpDatabase::class,
        GenerateConfigFiles::class,
        GenerateHash::class,
        HandleCache::class,
        HandleCourse::class,
        ListDatabase::class,
        MakeController::class,
        MakeBoot::class,
        MakeAuth::class,
        MakeCommand::class,
        MakeMiddleware::class,
        MakeMigrations::class,
        MakeModel::class,
        MakeQueue::class,
        MakeRouter::class,
        MakeSchedule::class,
        MakeSeeder::class,
        MakeValidator::class,
        Migrations::class,
        Queues::class,
        Scanner::class,
        Schedule::class,
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
