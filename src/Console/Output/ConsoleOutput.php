<?php

namespace Solital\Core\Console\Output;

class ConsoleOutput extends JobStatusConsole
{
    /**
     * @var string
     */
    protected static string $message;

    /**
     * @var string
     */
    protected static string $color_reset = '';

    /**
     * @var string
     */
    protected static string $color_success = '';

    /**
     * @var string
     */
    protected static string $color_info = '';

    /**
     * @var string
     */
    protected static string $color_warning = '';

    /**
     * @var string
     */
    protected static string $color_error = '';

    /**
     * @var string
     */
    protected static string $color_line = '';

    /**
     * Get the value of message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return self::$message;
    }

    /**
     * Print a single message on CLI
     * 
     * @param string $message
     * 
     * @return ConsoleOutput
     */
    public function printMessage(mixed $message): ConsoleOutput
    {
        self::$message = $message;
        echo self::$message;

        return $this;
    }

    /**
     * Create a success message
     *
     * @param mixed $message
     * @param bool $space
     * 
     * @return static 
     */
    public static function success(mixed $message, bool $space = false): static
    {
        self::generateColors();
        self::$message = self::prepareColor($message, self::$color_success, $space);
        return new static;
    }

    /**
     * Create a info message
     *
     * @param mixed $message
     * @param bool $space
     * 
     * @return static 
     */
    public static function info(mixed $message, bool $space = false): static
    {
        self::generateColors();
        self::$message = self::prepareColor($message, self::$color_info, $space);
        return new static;
    }

    /**
     * Create a warning message
     *
     * @param mixed $message
     * @param bool $space
     * 
     * @return static 
     */
    public static function warning(mixed $message, bool $space = false): static
    {
        self::generateColors();
        self::$message = self::prepareColor($message, self::$color_warning, $space);
        return new static;
    }

    /**
     * Create a error message
     *
     * @param mixed $message
     * @param bool $space
     * 
     * @return static 
     */
    public static function error(mixed $message, bool $space = false): static
    {
        self::generateColors();
        self::$message = self::prepareColor($message, self::$color_error, $space);
        return new static;
    }

    /**
     * Create a normal message
     *
     * @param mixed $message
     * @param bool $space
     * 
     * @return static 
     */
    public static function line(mixed $message, bool $space = false): static
    {
        self::generateColors();
        self::$message = self::prepareColor($message, self::$color_line, $space);
        return new static;
    }

    /**
     * Create a message with custom color
     *
     * @param mixed $message
     * @param int|ColorsEnum $color
     * @param bool $space
     * 
     * @return static
     */
    public static function message(mixed $message, int|ColorsEnum $color, bool $space = false): static
    {
        self::generateColors();

        (!is_int($color)) ?
            $color_value = $color->value :
            $color_value = ColorsEnum::CUSTOM->value . $color . "m";

        self::$message = self::prepareColor($message, $color_value, $space);
        return new static;
    }

    /**
     * Print a large banner in CLI
     *
     * @param mixed $message
     * @param int|ColorsEnum $color
     * @param int $lenght
     * 
     * @return static
     * @throws OutputException
     */
    public static function banner(mixed $message, int|ColorsEnum $color, int $lenght = 60): static
    {
        self::generateColors();

        if (!is_int($color)) {
            if (!str_starts_with($color->name, "BG_")) {
                throw new OutputException("Color must start with BG_", $color->name);
            }

            $color_value = $color->value;
        } else {
            $color_value = ColorsEnum::CUSTOM->value . 234 . 'm' . ColorsEnum::BG_CUSTOM->value . $color . "m";
        }

        self::$message = PHP_EOL;
        self::$message .= $color_value . str_pad('', $lenght, pad_type: STR_PAD_BOTH) . ColorsEnum::RESET->value . PHP_EOL;
        self::$message .= $color_value . str_pad($message, $lenght, pad_type: STR_PAD_BOTH) . ColorsEnum::RESET->value . PHP_EOL;
        self::$message .= $color_value . str_pad('', $lenght, pad_type: STR_PAD_BOTH) . ColorsEnum::RESET->value . PHP_EOL;
        self::$message .= PHP_EOL;
        return new static;
    }

    /**
     * Create an debug message with alert
     *
     * @param mixed $message
     * @param string $title
     * @param int|ColorsEnum $color
     * 
     * @return static
     * @throws OutputException
     */
    public static function debugMessage(mixed $message, string $title = 'INFO', mixed $color = null): static
    {
        self::generateColors();

        if (is_int($color)) {
            $color_value = ColorsEnum::CUSTOM->value . 234 . 'm' . ColorsEnum::BG_CUSTOM->value . $color . "m";
        } elseif (!is_null($color)) {
            if (!str_starts_with($color->name, "BG_")) {
                throw new OutputException("Color must start with BG_", $color->name);
            }

            $color_value = $color->value;
        } else {
            $color_value = ColorsEnum::BG_BLUE->value;
        }

        echo PHP_EOL;
        self::$message = '  ' . $color_value . ' ' . strtoupper($title) . ' ' .
            self::$color_reset . '   ' . $message;

        return new static;
    }

    /**
     * Clear console line
     *
     * @param int|null $seconds
     * 
     * @return void
     */
    public static function clear(?int $seconds = null): void
    {
        if (!is_null($seconds)) sleep($seconds);
        echo ColorsEnum::CLEAR->value;
    }

    /**
     * Write message on CLI
     * 
     * @return ConsoleOutput
     */
    public function print(): ConsoleOutput
    {
        echo self::$message;
        return $this;
    }

    /**
     * Break a line
     * 
     * @param bool|int $repeat Break another line
     * 
     * @return ConsoleOutput
     */
    public function break(bool|int $repeat = false): ConsoleOutput
    {
        echo PHP_EOL;

        if (is_int($repeat)) {
            for ($i = 0; $i <= $repeat; $i++) {
                echo PHP_EOL;
            }
        }

        if ($repeat == true) echo PHP_EOL . PHP_EOL;
        return $this;
    }

    /**
     * Call `exit()` function
     *
     * @param string|int $status
     * 
     * @return never 
     */
    public function exit(string|int $status = 0): never
    {
        exit($status);
    }

    /**
     * Get all 256 foreground colors
     *
     * @return mixed
     */
    public static function getForegroundColors(): mixed
    {
        if (self::generateColors() == false) return "Color not supported for your terminal";
        ob_start();

        for ($i = 1; $i <= 256; $i++) {
            echo "\033[38;5;" . $i . "m" . $i . " ";
        }

        echo ColorsEnum::RESET->value;
        $colors = ob_get_contents();
        ob_end_clean();
        return $colors;
    }

    /**
     * Get all 256 background colors
     *
     * @return mixed
     */
    public static function getBackgroundColors(): mixed
    {
        if (self::generateColors() == false) return "Color not supported for your terminal";
        ob_start();

        for ($i = 1; $i <= 256; $i++) {
            echo "\033[48;5;" . $i . "m" . $i . " ";
        }

        echo ColorsEnum::RESET->value;
        $colors = ob_get_contents();
        ob_end_clean();
        return $colors;
    }

    /**
     * Add message to a color
     *
     * @param mixed $message
     * @param string $color
     * @param bool $space
     * 
     * @return string
     */
    private static function prepareColor(mixed $message, string $color, bool $space): string
    {
        ($space == true) ? $space_value = "  " : $space_value = "";
        return $space_value . $color . $message . self::$color_reset;
    }

    /**
     * Generate colors for CLI
     * 
     * @return bool
     * @throws OutputException
     */
    protected static function generateColors(): bool
    {
        if (self::isCli() == false) throw new OutputException("Console Output is used only in CLI mode");

        if (self::colorIsSupported() || self::are256ColorsSupported()) {
            self::$color_reset = ColorsEnum::RESET->value;
            self::$color_success = ColorsEnum::LIGHT_GREEN->value;
            self::$color_info = ColorsEnum::LIGHT_CYAN->value;
            self::$color_warning = ColorsEnum::LIGHT_YELLOW->value;
            self::$color_error = ColorsEnum::BG_RED->value;
            self::$color_line = ColorsEnum::WHITE->value;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private static function colorIsSupported(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            if (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT)) {
                return true;
            } elseif (getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON') {
                return true;
            }

            return false;
        } else {
            return function_exists('posix_isatty') && posix_isatty(STDOUT);
        }
    }

    /**
     * @return bool
     */
    private static function are256ColorsSupported(): bool
    {
        return (DIRECTORY_SEPARATOR === '\\') ?
            function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT) :
            str_starts_with(getenv('TERM'), '256color');
    }

    /**
     * @return bool
     */
    private static function isCli(): bool
    {
        if (defined('STDIN')) return true;

        if (
            empty($_SERVER['REMOTE_ADDR']) &&
            !isset($_SERVER['HTTP_USER_AGENT'])
        ) {
            return true;
        }

        return false;
    }
}
