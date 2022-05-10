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
    public function verifyDefaultCommand(string $command, array $arguments = []): void
    {
        foreach ($this->default_commands as $method => $class) {
            if (strcmp($command, $method) === 0) {
                if (method_exists(__TRAIT__, $command)) {
                    $this->$method($command, $arguments);
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
                    $cmd = (new $class(null))->getCommand();

                    if (in_array($cmd, $arguments)) {
                        $instance = new $class(null);

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

        $this->error("Command not found")->print()->break()->exit();
    }

    /**
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    private function list(string $command, array $arguments = []): void
    {
        $res = $this->getCommandClass();

        foreach ($res as $res) {
            if (isset($res)) {
                foreach ($res as $class) {
                    $value = new $class(null);
                    $all_cmd = $value->getCommand();
                    $all_description = $value->getDescription();
                    $all_commands[$all_cmd] = $all_description;
                }
            }
        }

        ksort($all_commands);

        foreach ($all_commands as $key => $values) {
            $this->all_commands[$key] = $values;
        }

        $console = $this->line("Vinci Console ")->getMessage();
        $version = $this->success($this->getVersion())->getMessage();

        echo $console . $version . PHP_EOL . PHP_EOL;

        $this->warning("Usage:")->print()->break();
        $this->line("command <argument>", true)->print()->break(true);

        $this->warning("All commands")->print()->break();
        TableBuilder::formattedArray($this->all_commands, margin: true);
    }

    /**
     * @param string $command
     * @param array $arguments
     * 
     * @return void
     */
    private function about(string $command, array $arguments = []): void
    {
        $about = $this->line("Vinci Console ")->getMessage();
        $version = $this->success(self::getVersion())->getMessage();
        $date = $this->line(" (" . self::getDateVersion() . ")")->getMessage();

        echo $about . $version . $date . PHP_EOL . PHP_EOL;
        $this->line("PHP Version (" . PHP_VERSION . ")")->print()->break();
        $this->line("By Solital Framework. Access http://solitalframework.com/")->print()->break();
    }
}
