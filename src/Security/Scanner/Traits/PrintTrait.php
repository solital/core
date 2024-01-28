<?php

namespace Solital\Core\Security\Scanner\Traits;

use Solital\Core\Console\MessageTrait;

trait PrintTrait
{
    use MessageTrait;

    /**
     * @var string
     */
    protected string $outputFormat = '';

    /**
     * @var array
     */
    protected array $found_malwawre = [];

    /**
     * Pretty Colors
     *
     * @var string
     */
    protected string $ANSI_GREEN = "\033[32m";
    protected string $ANSI_RED = "\033[31m";
    protected string $ANSI_YELLOW = "\033[33m";
    protected string $ANSI_BLUE = "\033[36m";
    protected string $ANSI_OFF = "\033[0m";

    /**
     * Allows the -n/--no-color flag to easily remove color characters.
     *
     * @return void
     */
    protected function disableColor(): void
    {
        $this->ANSI_GREEN = '';
        $this->ANSI_RED = '';
        $this->ANSI_YELLOW = '';
        $this->ANSI_BLUE = '';
        $this->ANSI_OFF = '';
    }

    /**
     * Prints the passed 'string' in red text, calls showHelp().
     * Exits
     *
     * @param string $msg
     * 
     * @return void
     */
    protected function error2(string $msg): void
    {
        echo $this->ANSI_RED . 'Error: ' . $msg . $this->ANSI_OFF . PHP_EOL;
        //$this->showHelp();
        echo PHP_EOL . $this->ANSI_RED . 'Quiting' . $this->ANSI_OFF . PHP_EOL;
        exit;
    }

    /**
     * Formats and prints the scan result output line by line.
     *
     * Depending on specified options, it will print:
     * - Status code
     * - Last Modified Time
     * - MD5 Hash
     * - File Path
     * - Pattern Matched
     * - The last comment to appear in the pattern file before this pattern
     * - Matching line number
     *
     * @param $found
     * @param $path
     * @param $pattern
     * @param $comment
     * @param $hash
     * @param $lineNumber
     * @param bool $inWhitelist
     * 
     * @return void
     */
    protected function printPath($found, $path, $pattern, $comment, $hash, $lineNumber, bool $inWhitelist = false): void
    {
        $default_format = '%S ';

        if (!$found && !$inWhitelist) {
            if ($this->flagHideOk) {
                return;
            }
            $state = 'OK';
            $hash = '                                ';
            $state_color = $this->ANSI_GREEN;
        } elseif ($inWhitelist) {
            if ($this->flagHideWhitelist) {
                return;
            }
            $state = 'WL';
            $state_color = $this->ANSI_YELLOW;
        } else {
            if ($this->flagHideErr) {
                return;
            }
            $state = 'ER';
            $state_color = $this->ANSI_RED;
        }

        //Include cTime
        if ($this->flagTime) {
            $changed_time = filectime($path);
            $ctime = date('H:i d-m-Y', $changed_time);
            $default_format .= '%T';
        } else {
            $ctime = '';
        }

        //Include Checksum/Hash
        if ($this->flagChecksum) {
            $default_format .= '%M ';
        }

        // '#' and {} included to prevent accidental script execution attempts
        // in the event that script output is pasted into a root terminal
        $default_format .= '# {%F} ';

        //'#' added again as code snippets have the potential to be valid shell commands
        if ($found) {
            if ($this->flagPattern) {
                $default_format .= '%P ';
            }
            if ($this->flagComments) {
                $default_format .= '%C ';
            }
            if ($this->flagLineNumber) {
                $default_format .= '# %L';
            }
        }

        if ($this->outputFormat) {
            $map = array(
                '%S' => $state,
                '%T' => $ctime,
                '%M' => $hash,
                '%F' => $path,
                '%P' => $pattern,
                '%C' => $comment,
                '%L' => $lineNumber,
            );
        } else {
            $map = array(
                '%S' => $state_color . '# ' . $state . $this->ANSI_OFF,
                '%T' => $this->ANSI_BLUE . $ctime . $this->ANSI_OFF,
                '%M' => $this->ANSI_BLUE . $hash . $this->ANSI_OFF,
                '%F' => $path,
                '%P' => $state_color . '#' . $pattern . $this->ANSI_OFF,
                '%C' => $this->ANSI_BLUE . $comment . $this->ANSI_OFF,
                '%L' => $lineNumber,
            );
        }

        if ($this->outputFormat) {
            $format = $this->outputFormat;
        } else {
            $format = trim($default_format);
        }

        $this->found_malwawre[] = str_replace(array_keys($map), array_values($map), $format) . PHP_EOL;
    }

    /**
     * Prints stats on the run
     *
     * @param int|null $start
     * @param string $dir
     * 
     * @return void
     */
    protected function report(?int $start, string $dir): void
    {
        $end = time();

        /* $this->info('Start time: ')->print();
        $this->line((string)date('Y-m-d H:m:s', $start))->print()->break(); */

        $this->info('End time: ')->print();
        $this->line((string)date('Y-m-d H:i:s', $end))->print()->break(true);

        $this->info('Total execution time: ')->print();
        $this->line(($end - $start))->print()->break();

        $this->info('Base directory: ')->print();
        $this->line($dir)->print()->break(true);

        $this->info('Total directories scanned: ')->print();
        $this->line($this->stat['directories'])->print()->break();

        $this->info('Total files scanned: ')->print();
        $this->line($this->stat['files_scanned'])->print()->break(true);

        if ($this->stat['files_infected'] == 0) {
            $this->success('No malware was found')->print();
        } else {
            $this->error('Total malware identified:')->print();
            $this->line(' ' . $this->stat['files_infected'])->print()->break(true);

            foreach ($this->found_malwawre as $found) {
                $this->line($found)->print()->break();
            }
        }
    }

    /**
     * Prints out the usage menu options
     *
     * @return void
     */
    protected function showHelp(): void
    {
        echo 'Usage: php scan.php -d <directory>' . PHP_EOL;
        echo '    -h                   --help               Show this help message' . PHP_EOL;
        echo '    -d <directory>       --directory          Directory for searching' . PHP_EOL;
        echo '    -e <file extension>  --extension          File Extension to Scan, can be used multiple times' . PHP_EOL;
        echo '    -E                   --scan-everything    Scan all files, with or without extensions' . PHP_EOL;
        echo '    -i <directory|file>  --ignore             Directory of file to ignore' . PHP_EOL;
        echo '    -a                   --all-output         Enables --checksum,--comment,--pattern,--time' . PHP_EOL;
        echo '    -b                   --base64             Scan for base64 encoded PHP keywords' . PHP_EOL;
        echo '    -m                   --checksum           Display MD5 Hash/Checksum of file' . PHP_EOL;
        echo '    -c                   --comment            Display comments for matched patterns' . PHP_EOL;
        echo '    -x                   --extra-check        Adds GoogleBot and htaccess to Scan List' . PHP_EOL;
        echo '    -l                   --follow-symlink     Follow symlinked directories' . PHP_EOL;
        echo '    -k                   --hide-ok            Hide results with \'OK\' status' . PHP_EOL;
        echo '    -r                   --hide-err           Hide results with \'ER\' status' . PHP_EOL;
        echo '    -w                   --hide-whitelist     Hide results with \'WL\' status' . PHP_EOL;
        echo '    -n                   --no-color           Disable color mode' . PHP_EOL;
        echo '    -s                   --no-stop            Continue scanning file after first hit' . PHP_EOL;
        echo '    -p                   --pattern            Show Patterns next to the file name' . PHP_EOL;
        echo '    -t                   --time               Show time of last file change' . PHP_EOL;
        echo '    -L                   --line-number        Display matching pattern line number in file' . PHP_EOL;
        echo '    -o                   --output-format      Custom defined output format' . PHP_EOL;
        echo '    -j <version>         --wordpress-version  Version of wordpress to get md5 signatures' . PHP_EOL;
        echo '                         --combined-whitelist Combined whitelist' . PHP_EOL;
        echo '                         --disable-stats      Disable statistics output' . PHP_EOL;
    }
}
