<?php

namespace Solital\Core\Console;

use Solital\Core\Console\MessageTrait;

trait DefaultCommandsTrait
{
    use MessageTrait;

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
                        $this->warning("Usage:")->print()->break();
                        $this->line($instance->getCommand(), true)->print()->break(true);

                        $this->warning("Description:")->print()->break();
                        $this->line($instance->getDescription(), true)->print()->break();

                        if (!empty($instance->getAllArguments())) {
                            echo PHP_EOL;
                            $this->warning("Arguments:")->print()->break();
                        }

                        foreach ($instance->getAllArguments() as $args) {
                            $this->line($args, true)->print()->break();
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
            $this->error("Command '" . $arguments[2] . "' not found")->print()->break()->exit();
        }

        $this->error("You must specify the command name after 'help'")->print()->break()->exit();
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

        $console = $this->line("Vinci Console ")->getMessage();
        $version = $this->success($this->getVersion())->getMessage();

        echo $console . $version . PHP_EOL . PHP_EOL;

        $this->warning("Usage:")->print()->break();
        $this->line("command <argument>", true)->print()->break(true);

        for ($i = 0; $i < count($all['cmd']); $i++) {
            $this->warning($all['type'][$i])->print()->break();

            if (isset($all_commands[$i])) {
                TableBuilder::formattedArray($all_commands[$i], margin: true);
            }
        }
    }

    /**
     * @return void
     */
    private function about(): void
    {
        $about = $this->info("Vinci Console ")->getMessage();
        $version = $this->success(self::getVersion())->getMessage();
        $date = $this->line(" (" . self::getDateVersion() . ")")->getMessage();
        $about2 = $this->info("PHP Version ")->getMessage();
        $php_version = $this->success(PHP_VERSION)->getMessage();
        $about3 = $this->line("By Solital Framework. Access ")->getMessage();
        $docs = $this->warning(Command::SITE_DOC)->getMessage();

        echo $about . $version . $date . PHP_EOL;
        echo $about2 . $php_version . PHP_EOL . PHP_EOL;
        echo $about3 . $docs . PHP_EOL;
    }
}
