<?php

namespace Solital\Core\Console;

use ModernPHPException\ModernPHPException;
use ReflectionClass;
use Solital\Core\Console\{CommandException, DefaultCommandsTrait, Output\ConsoleOutput};

class Command
{
    use DefaultCommandsTrait;

    public const VERSION = "4.5.4";
    public const DATE_VERSION = "Mar 04 2025";
    public const SITE_DOC = "https://solital.github.io/site/docs/4.x/vinci-console/";

    /**
     * @var string
     */
    protected string $command = "";

    /**
     * @var string
     */
    private static string $command_copy = "";

    /**
     * @var array
     */
    private array $all_command_class = [];

    /**
     * @var array
     */
    private static array $all_command_class_copy = [];

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * Check if is `call` method
     * 
     * @var bool
     */
    private static bool $is_call_method = false;

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
        if (!class_exists(ModernPHPException::class, false)) {
            $exc = new ModernPHPException();
            $exc->start();
        }

        $this->all_command_class = $class;
        self::$all_command_class_copy = $class;

        if ($this->all_command_class) {
            foreach ($this->all_command_class as $class) {
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
        if ($command == "" || !isset($command[1])) self::call('list');

        $this->filterInputCommand($command, $arguments);
        $this->notFoundClass();

        if (isset($this->instance)) {
            $this->validateAttribute($this->instance);
            $this->saveHistory($this->command);
            return $this->instance->handle((object)$this->all_arguments, (object)$this->all_options);
        }

        ConsoleOutput::error("Command `" . $this->command . "` not found")->print()->break();
        if (!empty($this->verify_commands)) $this->spellCheckCommand($this->verify_commands, $this->command);
        exit;
    }

    /**
     * Call another command inside a command class
     *
     * @param string $command Command to execute
     * @param array  $arguments Arguments as an array
     * 
     * @return mixed
     */
    public static function call(string $command, array $arguments = []): mixed
    {
        if (self::$is_call_method == true)
            throw new CommandException("`call` method is calling another `call` method");

        if (self::$command_copy === $command)
            throw new CommandException("You cannot call the same command (" . $command . ") in same class");

        self::$is_call_method = true;
        $cmd = new static(self::$all_command_class_copy);
        return $cmd->read($command, $arguments);
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
            throw new CommandException("Duplicate command: `" . $command . "` in " . $instance . " class");
        }
    }

    private function filterInputCommand(array|string $command, array $arguments): void
    {
        if (is_array($command)) {
            $this->command = $command[1];
            self::$command_copy = $command[1];
            $arguments = array_slice($command, 2);
        }

        if (is_string($command)) {
            $this->command = $command;
            self::$command_copy = $command;

            if (self::$is_call_method == false) $arguments = array_slice($arguments, 2);
        }

        $this->arguments = $arguments;
        $this->filter($this->arguments);
        $this->verifyDefaultCommand($this->command, $this->arguments, (object)$this->all_options);

        $command_classes = $this->getCommandClass();

        foreach ($command_classes as $command_class) {
            if (isset($command_class)) {
                foreach ($command_class as $class) {
                    (!class_exists($class)) ?
                        array_push($this->not_found_class, $class) :
                        $this->validateArguments($class);
                }
            }
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
        $reflection = new ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        if (
            !method_exists($instance, 'getAllArguments') ||
            !method_exists($instance, 'getAllOptions') ||
            !method_exists($instance, 'getCommand')
        ) {
            throw new CommandException(get_class($instance) . " not extends 'Command' class");
        }

        $args = $instance->getAllArguments();
        $options = $instance->getAllOptions();
        $cmd = $instance->getCommand();

        $this->repeatedCommands($cmd, $instance::class);

        if ($cmd == $this->command) {
            if (!empty($options)) $this->validateOptions($options, $this->command);

            if (count($this->arguments) > count($args))
                ConsoleOutput::error("This command (" . $this->command . ") only accepts " . count($args) . " argument(s)")->print()->exit();

            $this->instance = $instance;

            (count($args) == count($this->arguments) && !empty($this->arguments)) ?
                $this->all_arguments = array_combine($args, $this->arguments) :
                $this->all_arguments = $this->arguments;

            if (!empty($args) && empty($this->all_arguments)) {
                ConsoleOutput::error("Argument required for this command: " . $this->command)->print()->exit();
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
            ConsoleOutput::error("This command (" . $command . ") require an option:")->print()->break();

            $options_formatted = $this->implodeWithKeys("", array_fill_keys($options, ""), ", ");
            $options_formatted = rtrim($options_formatted, ", ");

            ConsoleOutput::success("\n  " . $options_formatted)->print()->exit();
        }

        $options_without_char = [];

        foreach ($options as $opt) {
            $opt = str_replace('--', '', $opt);
            $options_without_char[] = $opt;
        }

        foreach ($this->all_options as $key => $opt) {
            if (in_array($key . '=', $options_without_char)) {
                if ($opt === true)
                    ConsoleOutput::error("Value to option '--" . $key . "' is required")->print()->exit();

                continue;
            }

            if (!in_array($key, $options_without_char))
                ConsoleOutput::error("Option '--" . $key . "' is invalid")->print()->exit();
        }
    }

    private function implodeWithKeys(string $glue, array $array, string $symbol = '=')
    {
        return implode(
            $glue,
            array_map(
                function ($k, $v) use ($symbol) {
                    return $k . $symbol . $v;
                },
                array_keys($array),
                array_values($array)
            )
        );
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
        $class = new ReflectionClass($instance);
        $attributes = $class->getMethod('handle')->getAttributes();
        $attr_exists = false;

        foreach ($attributes as $attr) {
            if ($attr->getName() == 'Override') $attr_exists = true;
        }

        if ($attr_exists == false)
            ConsoleOutput::error("'Override' attribute not found in 'handle' method at '" . basename($instance::class) . "' class")->print()->exit();
    }

    /**
     * @return Command
     */
    private function notFoundClass(): Command
    {
        if (!empty($this->not_found_class)) {
            foreach ($this->not_found_class as $not_found_class) {
                ConsoleOutput::warning("WARNING! Class not found: ")->print();
                ConsoleOutput::warning($not_found_class)->print()->break(true);
            }
        }

        return $this;
    }

    /**
     * Check for similar comand
     * 
     * @param array $text1
     * @param string $text2
     * 
     * @return void
     */
    private function spellCheckCommand(array $text1, string $text2): void
    {
        $is_similar = true;
        $input_word = null;

        foreach ($text1 as $word) {
            if ($text2 != $word) {
                similar_text(strtolower($word), strtolower($text2), $perc);

                if ($perc >= 80) {
                    $is_similar = false;
                    $input_word = $word;
                }
            }
        }

        if ($is_similar == false)
            ConsoleOutput::info("\n Didn't you mean: " . $input_word . "?")->print()->break();
    }
}
