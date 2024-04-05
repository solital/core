<?php

namespace Solital\Core\Console\Output;

abstract class JobStatusConsole
{
    /**
     * @var float
     */
    private static float $start;

    /**
     * @var float
     */
    private static float $end;

    /**
     * @var string
     */
    private static string $job_name;

    /**
     * @var mixed
     */
    private static mixed $callable;

    /**
     * @var string
     */
    private string $message = '';

    /**
     * Check closure status
     *
     * @param string $job_name
     * @param \Closure|callable $closure
     * 
     * @return static
     */
    public static function status(string $job_name, \Closure|callable $closure): static
    {
        self::$job_name = $job_name;
        self::$start = floor(microtime(true) * 1000);
        self::$callable = call_user_func($closure);
        self::$end = floor(microtime(true) * 1000);

        return new static;
    }

    /**
     * Check if job works or not
     *
     * @param string|null $message_true
     * @param string|null $message_false
     * 
     * @return void
     */
    public function printStatus(
        ?string $message_true = null,
        ?string $message_false = null,
        bool $show_microtime = true
    ): void {
        $total_time = self::$end - self::$start;
        $job_name = str_pad(self::$job_name, 100, '.');

        ConsoleOutput::line($job_name)->print();

        if ($show_microtime === true) {
            ConsoleOutput::line(str_pad($total_time . "ms", 7))->print();
        }

        if (is_bool(self::$callable)) {
            if (self::$callable === true) {
                (is_null($message_true)) ? $this->message = 'OK' : $this->message = $message_true;
                $color_status = 49;
            } else {
                (is_null($message_false)) ? $this->message = 'ERROR' : $this->message = $message_false;
                $color_status = 203;
            }
        } else {
            $this->message = 'DONE';
            $color_status = 49;
        }

        ConsoleOutput::message(" " . strtoupper($this->message), $color_status)->print()->break();
    }
}
