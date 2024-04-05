<?php

namespace Solital\Core\Console;

use Solital\Core\Console\Output\ConsoleOutput;

trait DefaultCommandsTrait
{
    /**
     * @var array
     */
    protected array $default_commands = [
        'help' => 'help',
        'about' => 'about',
        'list' => 'list'
    ];

    /**
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    public function verifyDefaultCommand(string $command, array $arguments = [], object $options = null): void
    {
        foreach ($this->default_commands as $method => $class) {
            if (strcmp($command, (string) $method) === 0) {
                if (method_exists(__TRAIT__, $command)) {
                    $this->$method($command, $arguments, $options);
                    exit;
                }
            }
        }
    }

    /**
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    private function help(string $command, array $arguments = []): void
    {
        $exists = null;
        $res = $this->getCommandClass();

        foreach ($res as $res) {
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
     * @return void
     */
    private function list(): void
    {
        $all = [
            'cmd' => [],
            'type' => []
        ];

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

        $console = ConsoleOutput::line("Vinci Console ")->getMessage();
        $version = ConsoleOutput::success($this->getVersion())->getMessage();

        echo $console . $version . PHP_EOL . PHP_EOL;

        ConsoleOutput::warning("Usage:")->print()->break();
        ConsoleOutput::line("command <argument>", true)->print()->break(true);

        for ($i = 0; $i < count($all['cmd']); $i++) {
            ConsoleOutput::warning($all['type'][$i])->print()->break();

            if (isset($all_commands[$i])) {
                Table::formattedRowData($all_commands[$i], margin: true);
            }
        }
    }

    /**
     * @return void
     */
    private function about(): void
    {
        $about = ConsoleOutput::info("Vinci Console ")->getMessage();
        $version = ConsoleOutput::success(self::getVersion())->getMessage();
        $date = ConsoleOutput::line(" (" . self::getDateVersion() . ")")->getMessage();
        $about2 = ConsoleOutput::info("PHP Version ")->getMessage();
        $php_version = ConsoleOutput::success(PHP_VERSION)->getMessage();
        $about3 = ConsoleOutput::line("By Solital Framework. Access ")->getMessage();
        $docs = ConsoleOutput::warning(Command::SITE_DOC)->getMessage();

        echo $about . $version . $date . PHP_EOL;
        echo $about2 . $php_version . PHP_EOL . PHP_EOL;
        echo $about3 . $docs . PHP_EOL;
    }
}
