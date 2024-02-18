<?php

namespace Solital\Core\Console;

use Solital\Core\Console\MessageTrait;

class InputOutput
{
    use MessageTrait;

    /**
     * @var string
     */
    private string $message_console;

    /**
     * @var string
     */
    private string $confirm;

    /**
     * @var string
     */
    private string $refuse;

    /**
     * @var bool
     */
    private bool $case_sensitive;

    /**
     * @var string
     */
    private string $color = '';

    /**
     * @param string $color
     */
    public function __construct(string $color = '')
    {
        if ($color != '') {
            $this->color = match ($color) {
                'green' => 'green',
                'yellow' => 'yellow',
                'blue' => 'blue',
                default => throw new CommandException("Color '" . $color . "' not exists")
            };
        }
    }

    /**
     * @param string $message
     * @param string $confirm
     * @param string $refuse
     * 
     * @return InputOutput
     */
    public function confirmDialog(string $message, string $confirm, string $refuse, bool $case_sensitive = true): InputOutput
    {
        $read_message = $message . ": [$confirm, $refuse] ";

        if ($this->color != '') {
            if ($this->color == 'green') {
                $read_message = $this->success($read_message)->getMessage();
            } else if ($this->color == 'yellow') {
                $read_message = $this->warning($read_message)->getMessage();
            } else if ($this->color == 'blue') {
                $read_message = $this->info($read_message)->getMessage();
            }
        } else {
            $read_message = $this->line($read_message)->getMessage();
        }

        $this->message_console = $this->readlineWithUnicodeSupport($read_message);
        $this->confirm = $confirm;
        $this->refuse = $refuse;
        $this->case_sensitive = $case_sensitive;

        return $this;
    }

    /**
     * @param string $message
     * 
     * @return InputOutput
     */
    public function dialog(string $message): InputOutput
    {
        if ($this->color != '') {
            if ($this->color == 'green') {
                $message = $this->success($message)->getMessage();
            } else if ($this->color == 'yellow') {
                $message = $this->warning($message)->getMessage();
            } else if ($this->color == 'blue') {
                $message = $this->info($message)->getMessage();
            }
        } else {
            $message = $this->line($message)->getMessage();
        }

        $this->message_console = $this->readlineWithUnicodeSupport($message);
        return $this;
    }

    /**
     * @param callable $callback
     * 
     * @return void
     */
    public function action(callable $callback): void
    {
        call_user_func($callback, $this->message_console);
    }

    /**
     * @param callable $callback
     * 
     * @return InputOutput
     */
    public function confirm(callable $callback): InputOutput
    {
        if ($this->case_sensitive == true) {
            if (str_contains($this->confirm, $this->message_console)) {
                call_user_func($callback);
                exit;
            }
        } else if ($this->case_sensitive == false) {
            if (strcasecmp($this->confirm, $this->message_console) === 0) {
                call_user_func($callback);
                exit;
            }
        }

        return $this;
    }

    /**
     * @param callable $callback
     * 
     * @return void
     */
    public function refuse(callable $callback): void
    {
        if ($this->case_sensitive == true) {
            if (str_contains($this->refuse, $this->message_console)) {
                call_user_func($callback);
                exit;
            }
        } else if ($this->case_sensitive == false) {
            if (strcasecmp($this->refuse, $this->message_console) === 0) {
                call_user_func($callback);
                exit;
            }
        }

        $this->error("Option not found")->print()->break()->exit();
    }

    /**
     * For some reason readline() doesn't support unicode, readline STRIPS æøåÆØÅ - 
     * for a readline function with unicode support, try
     *
     * @param string|null $prompt
     * 
     * @return string|false
     */
    private function readlineWithUnicodeSupport(?string $prompt = null): string|false /*: string|false*/
    {
        if ($prompt !== null && $prompt !== '') {
            echo $prompt;
        }

        $line = fgets(STDIN);

        // readline() removes the trailing newline, fgets does not,
        // to emulate the real readline(), we also need to remove it
        if ($line !== false && strlen($line) >= strlen(PHP_EOL) && str_ends_with($line, PHP_EOL)) {
            $line = substr($line, 0, -strlen(PHP_EOL));
        }

        return $line;
    }
}
