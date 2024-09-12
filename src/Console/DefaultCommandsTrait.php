<?php

namespace Solital\Core\Console;

use Solital\Core\Console\Output\ColorsEnum;
use Solital\Core\Console\Output\ConsoleOutput;

trait DefaultCommandsTrait
{
    /**
     * @var array
     */
    protected array $default_commands = [
        'help' => 'help',
        'history' => 'history',
        'clearHistory' => 'clear-history',
        'about' => 'about',
        'list' => 'list'
    ];

    /**
     * Verify if command is internal
     * 
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    public function verifyDefaultCommand(string $command, array $arguments = [], ?object $options = null): void
    {
        foreach ($this->default_commands as $method => $class) {
            $command = str_replace(["-"], "", $command);

            if (strcasecmp($command, $method) === 0) {
                if (method_exists(__TRAIT__, $method)) {
                    $this->saveHistory($command);
                    call_user_func_array([$this, $method], [$command, $arguments, $options]);
                    exit;
                }
            }
        }
    }

    /**
     * Help about a command
     * 
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    private function help(string $command, array $arguments = []): void
    {
        $exists = null;
        $result = $this->getCommandClass();

        foreach ($result as $res) {
            if (isset($res)) {
                foreach ($res as $class) {
                    $reflection = new \ReflectionClass($class);
                    $instance = $reflection->newInstanceWithoutConstructor();
                    $cmd = $instance->getCommand();

                    if (in_array($cmd, $arguments)) {
                        ConsoleOutput::warning("Usage:")->print()->break();
                        ConsoleOutput::line($instance->getCommand(), true)->print()->break(true);

                        ConsoleOutput::warning("Description:")->print()->break();
                        ConsoleOutput::line($instance->getDescription(), true)->print()->break();

                        if (!empty($instance->getAllArguments())) {
                            echo PHP_EOL;
                            ConsoleOutput::warning("Arguments:")->print()->break();
                        }

                        foreach ($instance->getAllArguments() as $args) {
                            ConsoleOutput::line($args, true)->print()->break();
                        }

                        $exists == true;
                        exit;
                    } else {
                        $exists == false;
                        continue;
                    }
                }
            }
        };

        if (isset($arguments[2])) {
            ConsoleOutput::error("Command '" . $arguments[2] . "' not found")->print()->break()->exit();
        }

        ConsoleOutput::error("You must specify the command name after 'help'")->print()->break()->exit();
    }

    /**
     * List all internal Vinci commands
     * 
     * @return void
     */
    private function list(): void
    {
        $all_commands = [];
        $all = ['cmd' => [], 'type' => []];

        $command_class = $this->getCommandClass();
        $type_commands = $this->getTypeCommands();

        foreach ($command_class as $cmd) {
            $all['cmd'][] = $cmd;
        }

        foreach ($type_commands as $type) {
            $all['type'][] = $type;
        }

        foreach ($all['cmd'] as $key => $cmd_class) {
            if (isset($cmd_class)) {
                foreach ($cmd_class as $class) {
                    $reflection = new \ReflectionClass($class);
                    $command_class = $reflection->newInstanceWithoutConstructor();

                    $reflection = new \ReflectionClass($command_class);
                    $class_commands = $reflection->getMethod('getCommand')->invoke($command_class);
                    $class_description = $reflection->getMethod('getDescription')->invoke($command_class);

                    $all_commands[$key][$class_commands] = $class_description;

                    ksort($all_commands[$key]);
                }
            }
        }

        if (empty($all_commands))
            ConsoleOutput::success("No commands found")->print()->exit();

        ConsoleOutput::banner("Commands available", ColorsEnum::BG_GREEN, 40)->print();
        ConsoleOutput::info(ColorsEnum::ITALIC->value . "Usage:")->print();
        ConsoleOutput::line("command <argument> <options>", true)->print()->break();
        echo PHP_EOL;

        for ($i = 0; $i < count($all['cmd']); $i++) {
            echo PHP_EOL;
            ConsoleOutput::warning(ColorsEnum::ITALIC->value . $all['type'][$i])->print()->break();

            if (isset($all_commands[$i])) {
                Table::formattedRowData($all_commands[$i], margin: true);
            }
        }
    }

    /**
     * Info about Vinci Console
     * 
     * @return void
     */
    private function about(): void
    {
        ConsoleOutput::banner(
            "Vinci Console " . self::getVersion() . " (" . self::getDateVersion() . ")",
            49,
            40
        )->print();

        Table::formattedRowData([
            "PHP Version" => PHP_VERSION,
            "Documentation" => Command::SITE_DOC
        ], 20);

        echo PHP_EOL;
    }

    /**
     * Get all command history
     *
     * @return never
     */
    private function history(): never
    {
        $cmd_history_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vinci-console' . DIRECTORY_SEPARATOR . 'vinci-history.log';

        if (file_exists($cmd_history_file)) {
            ConsoleOutput::banner("Vinci Console - history commands", ColorsEnum::BG_GREEN)->print();

            $commands = file_get_contents($cmd_history_file);
            $commands = explode("\n", $commands);
            $commands = array_slice($commands, 0, 10);

            foreach ($commands as $command) {
                ConsoleOutput::line($command)->print()->break();
            }

            clearstatcache(true, $cmd_history_file);
            exit;
        }

        ConsoleOutput::warning("History file not found")->print()->break()->exit();
    }

    /**
     * Clear a command history
     *
     * @return never
     */
    private function clearHistory(): never
    {
        $cmd_history_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vinci-console' . DIRECTORY_SEPARATOR . 'vinci-history.log';

        if (file_exists($cmd_history_file)) {
            unlink($cmd_history_file);
            ConsoleOutput::success("History file deleted with successfully!")->print()->exit();
        }

        clearstatcache(true, $cmd_history_file);
        exit;
    }

    /**
     * Create a command history
     *
     * @param string $command
     * 
     * @return void
     */
    protected function saveHistory(string $command): void
    {
        $cmd_history_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vinci-console' . DIRECTORY_SEPARATOR;

        if (!is_dir($cmd_history_dir)) mkdir($cmd_history_dir);

        file_put_contents(
            $cmd_history_dir . 'vinci-history.log',
            "[" . date('Y-m-d H:i:s') . "] " . $command . "\n",
            FILE_APPEND
        );
    }
}
