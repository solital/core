<?php

namespace Solital\Core\Console;

/**
 * @deprecated Use Solital\Core\Console\Output\ConsoleOutput class
 */
trait MessageTrait
{
    /**
     * @var string
     */
    protected static string $message;

    /**
     * @var mixed|null
     */
    protected static mixed $color_reset = null;

    /**
     * @var mixed|null
     */
    protected static mixed $color_success = null;

    /**
     * @var mixed|null
     */
    protected static mixed $color_info = null;

    /**
     * @var mixed|null
     */
    protected static mixed $color_warning = null;

    /**
     * @var mixed|null
     */
    protected static mixed $color_error = null;

    /**
     * @var mixed|null
     */
    protected static mixed $color_line = null;

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
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput class
     * @param string $message
     * 
     * @return self
     */
    public function printMessage(mixed $message): self
    {
        self::$message = $message;
        echo self::$message;

        return $this;
    }

    /**
     * Create a success message
     * 
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput::success()
     * @param mixed $message
     * @param bool $space
     * 
     * @return self 
     */
    public function success(mixed $message, bool $space = false): self
    {
        $this->generateColors();

        self::$message = $message;

        if ($space == true) {
            self::$message = "  " . self::$color_success . self::$message . self::$color_reset;
        } else {
            self::$message = self::$color_success . self::$message . self::$color_reset;
        }

        return $this;
    }

    /**
     * Create a info message
     *
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput::info()
     * @param mixed $message
     * @param bool $space
     * 
     * @return self 
     */
    public function info(mixed $message, bool $space = false): self
    {
        $this->generateColors();

        self::$message = $message;

        if ($space == true) {
            self::$message = "  " . self::$color_info . self::$message . self::$color_reset;
        } else {
            self::$message = self::$color_info . self::$message . self::$color_reset;
        }

        return $this;
    }

    /**
     * Create a warning message
     *
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput::warning()
     * @param mixed $message
     * @param bool $space
     * 
     * @return self 
     */
    public function warning(mixed $message, bool $space = false): self
    {
        $this->generateColors();

        self::$message = $message;

        if ($space == true) {
            self::$message = "  " . self::$color_warning . self::$message . self::$color_reset;
        } else {
            self::$message = self::$color_warning . self::$message . self::$color_reset;
        }

        return $this;
    }

    /**
     * Create a error message
     *
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput::error()
     * @param mixed $message
     * @param bool $space
     * 
     * @return self 
     */
    public function error(mixed $message, bool $space = false): self
    {
        $this->generateColors();

        self::$message = $message;

        if ($space == true) {
            self::$message = "  " . self::$color_error . self::$message . self::$color_reset;
        } else {
            self::$message = self::$color_error . self::$message . self::$color_reset;
        }

        return $this;
    }

    /**
     * Create a normal message
     *
     * @deprecated Use Solital\Core\Console\Output\ConsoleOutput::line()
     * @param mixed $message
     * @param bool $space
     * 
     * @return self 
     */
    public function line(mixed $message, bool $space = false): self
    {
        $this->generateColors();

        self::$message = $message;

        if ($space == true) {
            self::$message = "  " . self::$color_line . self::$message . self::$color_reset;
        } else {
            self::$message = self::$color_line . self::$message . self::$color_reset;
        }

        return $this;
    }

    /**
     * Write message on CLI
     * 
     * @return self
     */
    public function print(): self
    {
        echo self::$message;

        return $this;
    }

    /**
     * Break a line
     * 
     * @param bool $repeat Break another line
     * 
     * @return self
     */
    public function break(bool $repeat = false): self
    {
        if ($repeat == true) {
            echo PHP_EOL . PHP_EOL;
        } else {
            echo PHP_EOL;
        }

        return $this;
    }

    /**
     * Call `exit()` function
     * 
     * @return void
     */
    public function exit(): void
    {
        exit;
    }

    /**
     * @return self
     */
    private function generateColors(): self
    {
        if ($this->colorIsSupported() || $this->are256ColorsSupported()) {
            self::$color_reset = "\e[0m";
            self::$color_success = "\033[92m";
            self::$color_info = "\033[96m";
            self::$color_warning = "\033[93m";
            self::$color_error = "\033[41m";
            self::$color_line = "\033[97m";
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function colorIsSupported(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            if (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT)) {
                return true;
            } elseif (getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON') {
                return true;
            }
            return false;
        } else {
            return function_exists('posix_isatty') && @posix_isatty(STDOUT);
        }
    }

    /**
     * @return bool
     */
    private function are256ColorsSupported(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT);
        } else {
            return str_starts_with(getenv('TERM'), '256color');
        }
    }
}
