<?php

namespace Solital\Core\Console;

use ModernPHPException\ModernPHPException;
use Solital\Core\Console\CommandException;
use Solital\Core\Console\{MessageTrait, DefaultCommandsTrait};

class Command
{
    use DefaultCommandsTrait;
    use MessageTrait;

    const VERSION = "3.3.1";
    const DATE_VERSION = "Nov 24 2023";
    const SITE_DOC = "https://solital.github.io/site/docs/3.x/vinci-console/";

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
    private array $all_arguments = [];

    /**
     * @var array
     */
    protected array $command_class = [];

    /**
     * @var array
     */
    private array $verify_commands = [];

    /**
     * @var mixed
     */
    private mixed $instance;

    /**
     * @var array
     */
    protected array $type_commands = [];

    /**
     * @var array
     */
    private array $not_found_class = [];

    /**
     * @param array $class
     */
    public function __construct($class)
    {
        (new ModernPHPException)->start();

        if ($class) {
            foreach ($class as $class) {
                $instance = new $class();
                $this->command_class[] = $instance->getCommandClass();
                $this->type_commands[] = $instance->getTypeCommands();
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
        $this->verifyDefaultCommand($this->command, $this->arguments, (object)$this->options);

        $command_class = $this->getCommandClass();

        foreach ($command_class as $command_class) {
            if (isset($command_class)) {
                foreach ($command_class as $class) {
                    if (!class_exists($class)) {
                        array_push($this->not_found_class, $class);
                    } else {
                        $instance = new $class(null);
                        $args = $instance->getAllArguments();
                        $cmd = $instance->getCommand();

                        $this->repeatedCommands($cmd, get_class($instance));

                        if ($cmd == $this->command) {
                            $this->instance = $instance;

                            if (count($args) == count($this->arguments) && !empty($this->arguments)) {
                                $this->all_arguments = array_combine($args, $this->arguments);
                            } else {
                                $this->all_arguments = $this->arguments;
                            }
                        }
                    }
                }
            }
        }

        $this->notFoundClass();

        if (isset($this->instance)) {
            return $this->instance->handle((object)$this->all_arguments, (object)$this->options);
        }

        //self::log("CommandNotFound", "Command '" . $this->command . "' not found");
        $this->error("Command '" . $this->command . "' not found")->print()->break()->exit();

        return $this;
    }

    /**
     * @param string $file_name
     * @param string $description
     * 
     * @return void
     * 
     */
    /* public static function log(string $file_name, string $description = ""): void
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file_name . "-" . date("Y-m-d");

        if (file_exists($file)) {
            $log_description = file_get_contents($file);
            $log_description .= $description;

            file_put_contents(
                $file,
                $log_description . " [" . date('Y-m-d - H:i:s') . "]" . "\n"
            );
        } else {
            file_put_contents(
                $file,
                $description . " [" . date('Y-m-d - H:i:s') . "]" . "\n"
            );
        }
    } */

    /**
     * Get the value of description
     * 
     * @return string
     */
    public function getDescription(): string
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
     * @return array
     */
    private function getCommandClass(): array
    {
        return $this->command_class;
    }

    /**
     * @return array
     */
    private function getTypeCommands(): array
    {
        return $this->type_commands;
    }

    /**
     * @param string $cmd
     * @param string $instance
     * 
     * @return void
     * @throws CommandException
     */
    private function repeatedCommands(string $cmd, string $instance): void
    {
        array_push($this->verify_commands, $cmd);
        $res = array_unique(array_diff_assoc($this->verify_commands, array_unique($this->verify_commands)));

        if (!empty($res)) {
            $command = implode(",", $res);
            throw new CommandException("Duplicate command: '" . $command . "' in " . $instance . " class");
        }
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

    /**
     * @return Command
     */
    private function notFoundClass(): Command
    {
        if (!empty($this->not_found_class)) {
            foreach ($this->not_found_class as $not_found_class) {
                $this->warning("WARNING! Class not found: ")->print();
                $this->warning($not_found_class)->print()->break();
            }
        }

        return $this;
    }
}
