<?php

namespace Solital\Core\Process;

class Process
{
    /**
     * @var mixed
     */
    protected static mixed $output;

    /**
     * @param string $cmd
     * @param string $output_file Creates a log file
     * @param string $pid_file
     * @param bool $mergestd_error Hide CLI issues
     * @param bool $bg Show complete error in terminal
     * 
     * @return self
     */
    public static function executeCommand(
        string $cmd,
        string $output_file = "",
        string $pid_file = "",
        bool $mergestd_error = true,
        bool $bg = false
    ): mixed {
        $fullcmd = $cmd;
        if (strlen($output_file) > 0) $fullcmd .= " >> " . $output_file;
        if ($mergestd_error) $fullcmd .= " 2>&1";

        if ($bg) {
            $fullcmd = "nohup " . $fullcmd . " &";
            if (strlen($pid_file)) $fullcmd .= " echo $! > " . $pid_file;
        } else {
            if (strlen($pid_file) > 0) $fullcmd .= "; echo $$ > " . $pid_file;
        }

        self::$output = shell_exec($fullcmd);

        return new static;
    }

    /**
     * @return mixed
     */
    public function getOutput(): mixed
    {
        return self::$output;
    }

    /**
     * @param string $command
     * @param string $log_file
     * 
     * @return true
     */
    public static function execInBackground(string $command, string $log_file): true
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            //windows
            pclose(popen("start /B " . $command . " 1> $log_file 2>&1", "r"));
        } else {
            //linux
            self::executeCommand($command . " 1> $log_file 2>&1");
        }

        return true;
    }
}
