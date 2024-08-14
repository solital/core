<?php

namespace Solital\Core\Kernel\Console;

use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Console\Output\{ColorsEnum, ConsoleOutput};
use Solital\Core\Kernel\{Application, DebugCore};

class SolitalCommands implements ExtendCommandsInterface
{
    /**
     * @var string
     */
    protected string $type_commands = "Solital Commands";

    public function __construct()
    {
        if (DebugCore::isCoreDebugEnabled()) {
            ConsoleOutput::banner("DEBUG ENABLED", ColorsEnum::BG_YELLOW, 20)->print()->break();
        }

        Application::getInstance();
    }

    /**
     * @var array
     */
    protected array $command_class = [];

    /**
     * @return array
     */
    public function getCommandClass(): array
    {
        $this->command_class = $this->getCommandsFiles();
        return $this->command_class;
    }

    /**
     * @return string
     */
    public function getTypeCommands(): string
    {
        return $this->type_commands;
    }

    /**
     * @return array
     */
    private static function getCommandsFiles(): array
    {
        $files = [];
        $classes = [];
        $cmd_dir = __DIR__ . DIRECTORY_SEPARATOR . "Commands" . DIRECTORY_SEPARATOR;
        $handle = Application::provider("handler-file");

        if (is_dir($cmd_dir)) {
            $all_files = $handle->folder($cmd_dir)->files();

            if (!is_null($all_files)) {
                foreach ($all_files as $file) {
                    $classes[] = basename($file, ".php");
                }

                foreach ($classes as $class) {
                    $files[] = "\Solital\Core\Kernel\Console\Commands\\" . $class;
                }
            }
        }

        clearstatcache();
        return $files;
    }
}
