<?php

namespace Solital\Core\Console;

use ModernPHPException\ModernPHPException;
use Solital\Core\Console\CommandException;
use Solital\Core\Console\{MessageTrait, DefaultCommandsTrait};

class Command
{
    use DefaultCommandsTrait;
    use MessageTrait;

    public const VERSION = "4.2.0";
    public const DATE_VERSION = "Mar xx 2024";
    public const SITE_DOC = "https://solital.github.io/site/docs/4.x/vinci-console/";

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
    protected array $all_options = [];

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
    public function __construct(array $class = [])
    {
        /* $exc = new ModernPHPException();
        $exc->start(); */

        if ($class) {
            foreach ($class as $class) {
                $instance = new $class();
                $this->command_class[] = $instance->getCommandClass();
                $this->type_commands[] = $instance->getTypeCommands();
            }
        }
    }

    /**
     * Read the command
     * 
     * @param string|array $command Command to execute
     * @param array        $arguments Arguments as an array
     * 
     * @return mixed
     */
    public function read(string|array $command = "", array $arguments = []): mixed
    {
        if (is_array($command)) {
            $this->command = $command[1];
            $arguments = array_slice($command, 2);
        }

        if (is_string($command)) {
            $this->command = $command;
            $arguments = array_slice($arguments, 2);
        }

        $this->arguments = $arguments;
        $this->filter($this->arguments);
        $this->verifyDefaultCommand($this->command, $this->arguments, (object)$this->all_options);

        $command_class = $this->getCommandClass();

        foreach ($command_class as $command_class) {
            if (isset($command_class)) {
                foreach ($command_class as $class) {
                    if (!class_exists($class)) {
                        array_push($this->not_found_class, $class);
                    } else {
                        $this->validateArguments($class);
                    }
                }
            }
        }

        $this->notFoundClass();

        if (isset($this->instance)) {
            $this->validateAttribute($this->instance);
            return $this->instance->handle((object)$this->all_arguments, (object)$this->all_options);
        }

        $this->error("Command '" . $this->command . "' not found")->print()->break()->exit();
        return $this;
    }

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
     * Get the value of options
     * 
     * @return null|array
     */
    public function getAllOptions(): ?array
    {
        return $this->options;
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
            if (str_starts_with((string) $options, "--")) {
                $this->raw_options[] = $options;
                $options_replace = str_replace("--", "", (string) $options);
                $options_replace = explode("=", $options_replace);
                $this->all_options[$options_replace[0]] = ($options_replace[1] ?? true);
            }
        }

        return $this->all_options;
    }

    /**
     * @param array $args
     * 
     * @return array|null
     */
    private function filterArgs(array $args): ?array
    {
        if (isset($args)) {
            $this->arguments = array_diff($args, $this->raw_options);
            return $this->arguments;
        }

        return null;
    }

    /**
     * Validate arguments if isset at classe
     * 
     * @param object|string $class
     * 
     * @return void
     */
    private function validateArguments(object|string $class): void
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $args = $instance->getAllArguments();
        $options = $instance->getAllOptions();
        $cmd = $instance->getCommand();

        $this->repeatedCommands($cmd, $instance::class);

        if ($cmd == $this->command) {
            if (!empty($options)) {
                $this->validateOptions($options, $this->command);
            }

            if (count($this->arguments) > count($args)) {
                $this->error("This command (" . $this->command . ") only accepts " . count($args) . " argument(s)")->print()->exit();
            }

            $this->instance = $instance;

            if (count($args) == count($this->arguments) && !empty($this->arguments)) {
                $this->all_arguments = array_combine($args, $this->arguments);
            } else {
                $this->all_arguments = $this->arguments;
            }

            if (!empty($args) && empty($this->all_arguments)) {
                $this->error("Argument required for this command: " . $this->command)->print()->exit();
            }
        }
    }

    /**
     * Validade options if options isset at class command
     * 
     * @param array $options
     * 
     * @return void
     */
    private function validateOptions(array $options, string $command): void
    {
        if (empty($this->all_options)) {
            $this->error("Error:")->print();
            $this->line(" This command (" . $command . ") require an option:")->print()->break(true);
            Table::formattedRowData(array_fill_keys($options, ''), margin: true);
            exit;
        }

        $options_without_char = [];

        foreach ($options as $opt) {
            $opt = str_replace('--', '', $opt);
            $options_without_char[] = $opt;
        }

        foreach ($this->all_options as $key => $opt) {
            if (in_array($key . '=', $options_without_char)) {
                if ($opt === true) {
                    $this->error("Value to option '--" . $key . "' is required")->print()->exit();
                }
            }

            if (!in_array($key, $options_without_char)) {
                $this->error("Option '--" . $key . "' is invalid")->print()->exit();
            }
        }
    }

    /**
     * Validate if attribute `Override` exists on method `handle`
     *
     * @param mixed $instance
     * 
     * @return void
     */
    private function validateAttribute(mixed $instance): void
    {
        $class = new \ReflectionClass($instance);
        $attributes = $class->getMethod('handle')->getAttributes();
        $attr_exists = false;

        foreach ($attributes as $attr) {
            if ($attr->getName() == 'Override') {
                $attr_exists = true;
            }
        }

        if ($attr_exists == false) {
            $this->error("'Override' attribute not found in 'handle' method at '" . basename($instance::class) . "' class")->print()->exit();
        }
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
