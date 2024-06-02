<?php

namespace Solital\Core\Console;

use Solital\Core\Console\Output\{ColorsEnum, ConsoleOutput};

/**
 * Enter an input value on the command line
 */
class InputOutput
{
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
     * @var ColorsEnum
     */
    private ?ColorsEnum $color = null;

    /**
     * Customize the colors of the message that is displayed from the CLI
     *
     * @param ColorsEnum $color
     * 
     * @return InputOutput
     */
    public function color(ColorsEnum $color): InputOutput
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Use a "yes/no" confirmation
     * 
     * @param string $message
     * @param string $confirm
     * @param string $refuse
     * 
     * @return InputOutput
     */
    public function confirmDialog(
        string $message,
        string $confirm,
        string $refuse,
        bool $case_sensitive = true
    ): InputOutput {
        if (is_null($this->color)) $this->color = ColorsEnum::WHITE;

        $opt = ConsoleOutput::warning("[$confirm, $refuse]")->getMessage();
        $message = ConsoleOutput::message($message, $this->color)->getMessage();
        $read_message = $message . ": " . $opt . " ";

        $this->message_console = $this->readlineWithUnicodeSupport($read_message);
        $this->confirm = $confirm;
        $this->refuse = $refuse;
        $this->case_sensitive = $case_sensitive;

        return $this;
    }

    /**
     * Display a message to the user to inform a input value
     * 
     * @param string $message
     * 
     * @return InputOutput
     */
    public function dialog(string $message): InputOutput
    {
        if (is_null($this->color)) $this->color = ColorsEnum::WHITE;
        $message = ConsoleOutput::message($message, $this->color)->getMessage();

        $this->message_console = $this->readlineWithUnicodeSupport($message);
        return $this;
    }

    /**
     * Performs an action using the previously entered value
     * 
     * @param callable $callback
     * 
     * @return never
     */
    public function action(callable $callback): never
    {
        call_user_func($callback, $this->message_console);
        exit;
    }

    /**
     * Confirm dialog
     * 
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
     * Refuse dialog
     * 
     * @param callable $callback
     * 
     * @return never
     */
    public function refuse(callable $callback): never
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

        ConsoleOutput::error("Option not found")->print()->break()->exit();
    }

    /**
     * Hide the user input and not echo it to screen
     *
     * @param string $message
     * 
     * @return string
     */
    public function password(string $message): string
    {
        if (is_null($this->color)) $this->color = ColorsEnum::WHITE;
        $message = ConsoleOutput::message($message, $this->color)->getMessage();

        if (PHP_OS == 'WINNT') {
            $pwd = shell_exec('C:\Windows\system32\WindowsPowerShell\v1.0\powershell.exe -Command "$Password=Read-Host -assecurestring \"' . $message . '\" ; $PlainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($Password)) ; echo $PlainPassword;"');
            $pwd = explode("\n", $pwd);
            $pwd = $pwd[0];
            return $pwd;
        }

        if ($message) print $message;
        $f = popen("read -s; echo \$REPLY", "r");
        $input = fgets($f, 100);
        pclose($f);
        print "\n";
        return $input;
    }

    /**
     * For some reason readline() doesn't support unicode, readline STRIPS æøåÆØÅ - 
     * for a readline function with unicode support, try
     *
     * @param string|null $prompt
     * 
     * @return string|false
     */
    private function readlineWithUnicodeSupport(?string $prompt = null): string|false
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
