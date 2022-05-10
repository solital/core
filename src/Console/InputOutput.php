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
     * @param string $message
     * @param string $confirm
     * @param string $refuse
     * 
     * @return InputOutput
     */
    public function confirmDialog(string $message, string $confirm, string $refuse, bool $case_sensitive = true): InputOutput
    {
        $this->message_console = readline($message . ": [$confirm, $refuse] ");
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
        $this->message_console = readline($message);
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
}
