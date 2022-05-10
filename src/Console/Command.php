<?php

namespace Solital\Core\Console;

use Solital\Core\Console\{MessageTrait, DefaultCommandsTrait};

class Command
{
    use DefaultCommandsTrait;
    use MessageTrait;

    const VERSION = "3.0.0";
    const DATE_VERSION = "May 10 2022";

    /**
     * @var string
     */
    protected string $command = "";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    private array $raw_options = [];

    /**
     * @var string
     */
    protected string $description = "";

    /**
     * @var array
     */
    private array $all_commands = [];

    /**
     * @var array
     */
    protected array $command_class = [];

    /**
     * @param array $class
     */
    public function __construct($class)
    {
        if ($class) {
            foreach ($class as $class) {
                $instance = new $class();
                $this->command_class[] = $instance->getCommandClass();
            }
        }
    }

    /**
     * @param string $command
     * @param array $arguments
     * 
     * @return mixed
     */
    public function read(string $command = "", array $arguments = []): mixed
    {
        $this->command = $command;
        $this->arguments = $arguments;

        $this->filter($this->arguments);
        $this->verifyDefaultCommand($this->command, $this->arguments);

        $res = $this->getCommandClass();

        foreach ($res as $res) {
            if (isset($res)) {
                foreach ($res as $class) {
                    $instance = new $class(null);
                    $args = $instance->getAllArguments();
                    $cmd = $instance->getCommand();

                    if (count($args) == count($this->arguments) && !empty($this->arguments)) {
                        $all_arguments = array_combine($args, $this->arguments);
                    } else {
                        $all_arguments = $this->arguments;
                    }

                    if ($cmd == $this->command) {
                        return $instance->handle((object)$all_arguments, (object)$this->options);
                    }
                }
            }
        }

        $this->error("Command not found")->print()->break()->exit();
    }

    /**
     * @param string $param
     * 
     * @return string
     */
    public function getOption(string $param): string
    {
        return $this->options[$param];
    }

    /**
     * @param array $param
     * 
     * @return string
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the value of command
     * 
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return null|array
     */
    public function getAllArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public static function getVersion(): mixed
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    public static function getDateVersion(): string
    {
        return self::DATE_VERSION;
    }

    /**
     * @param string $cmd
     */
    public function execInBackground(string $cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $cmd, "r"));
        } else {
            exec($cmd . " > /dev/null &", $output, $return_var);

            return $output;
        }
    }

    /**
     * @return array
     */
    private function getCommandClass(): array
    {
        return $this->command_class;
    }

    /**
     * @param array $args
     * 
     * @return Command
     */
    private function filter(array $args): Command
    {
        $this->filterOptions($args);
        $this->filterArgs($args);

        return $this;
    }

    /**
     * @param array $args
     * 
     * @return array|null
     */
    private function filterOptions(array $args): ?array
    {
        foreach ($args as $options) {
            if (str_starts_with($options, "--")) {
                $this->raw_options[] = $options;
                $options_replace = str_replace("--", "", $options);
                $options_replace = explode("=", $options_replace);
                $this->options[$options_replace[0]] = ($options_replace[1] ?? true);
            }
        }

        return $this->options;
    }

    /**
     * @param array $args
     * 
     * @return array|null
     */
    private function filterArgs(array $args): ?array
    {
        if (isset($args)) {
            unset($args[0]);
            unset($args[1]);
            $this->arguments = array_diff($args, $this->raw_options);

            return $this->arguments;
        }

        return null;
    }
}
