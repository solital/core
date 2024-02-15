<?php

namespace Solital\Core\Security\Scanner\Traits;

trait ScanTrait
{
    use PatternTrait;
    use PrintTrait;
    use WhiteListTrait;

    /**
     * @var array
     */
    protected array $stat = [
        'directories' => 0,
        'files_scanned' => 0,
        'files_infected' => 0,
    ];

    /**
     * @var bool
     */
    protected bool $flagBase64 = false;
    protected bool $flagChecksum = false;
    protected bool $flagComments = false;
    protected bool $flagHideOk = false;
    protected bool $flagHideErr = false;
    protected bool $flagHideWhitelist = false;
    protected bool $flagNoStop = false;
    protected bool $flagPattern = false;
    protected bool $flagTime = false;
    protected bool $flagExtraCheck = false;
    protected bool $flagFollowSymlink = false;
    protected bool $flagLineNumber = false;
    protected bool $flagScanEverything = false;
    protected bool $flagCombinedWhitelist = false;
    protected bool $flagDisableStats = false;

    /**

     * @param bool $b
     * 
     * @return void
     */
    public function setFlagChecksum(bool $b): void
    {
        $this->flagChecksum = $b;
    }

    /**

     * @param bool $b
     * 
     * @return void
     */
    public function setFlagComments(bool $b): void
    {
        $this->flagComments = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagPattern(bool $b): void
    {
        $this->flagPattern = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagTime(bool $b): void
    {
        $this->flagTime = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagLineNumber(bool $b): void
    {
        $this->flagLineNumber = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagBase64(bool $b): void
    {
        $this->flagBase64 = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagExtraCheck(bool $b): void
    {
        $this->flagExtraCheck = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagFollowSymlink(bool $b): void
    {
        $this->flagFollowSymlink = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagHideOk(bool $b): void
    {
        $this->flagHideOk = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagHideErr(bool $b): void
    {
        $this->flagHideErr = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagHideWhitelist(bool $b): void
    {
        $this->flagHideWhitelist = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagNoStop(bool $b): void
    {
        $this->flagNoStop = $b;
    }

    /**
     * @param array $b
     * 
     * @return void
     */
    public function setOutputFormat(array $format): void
    {
        $this->outputFormat = array_shift($format);
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagScanEverything(bool $b): void
    {
        $this->flagScanEverything = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagCombinedWhitelist(bool $b): void
    {
        $this->flagCombinedWhitelist = $b;
    }

    /**
     * @param bool $b
     * 
     * @return void
     */
    public function setFlagDisableStats(bool $b): void
    {
        $this->flagDisableStats = $b;
    }

    /**
     * Performs raw string, case sensitive matching.
     * Returns true if the raw string exists in the file contents.
     *
     * @param string $pattern
     * @param string $content
     * 
     * @return mixed
     */
    protected function scanFunc_STR(string $pattern, string $content): mixed
    {
        return strpos($content, (string)$pattern);
    }

    /**
     * Performs raw string, case insensitive matching.
     * Returns true if the raw string exists in the file contents, ignoring case.
     *
     * @param string $pattern
     * @param string $content
     * 
     * @return mixed
     */
    protected function scanFunc_STRI(string $pattern, string $content): mixed
    {
        return stripos($content, (string)$pattern);
    }


    /**
     * Performs regular expression matching.
     * Returns true if the Regular Expression matches something in the file.
     * Patterns will match multiple lines, though you can use ^$ to match the beginning and end of a line.
     *
     * @param string $pattern
     * @param string $content
     * 
     * @return mixed
     */
    protected function scanFunc_RE(string $pattern, string $content): mixed
    {
        $ret = preg_match('/' . $pattern . '/im', $content, $match, PREG_OFFSET_CAPTURE);

        if ($ret) {
            return $match[0][1];
        }

        return false;
    }

    /**
     * First parameter '$scanFunction' is a defined function name passed as a string.
     * This function should accept a pattern string and a content string.
     * This function will return true if the pattern exists in the content.
     * See 'scanFunc_STR', 'scanFunc_STRI', 'scanFUNC_RE' above as examples.
     *
     * Loops through all patterns in a file using the passed function name to determine a match.
     * Variables passed by reference for performance and modification access.
     */
    protected function scanLoop($scanFunction, &$fileContent, &$patterns, &$path, &$found, $hash)
    {
        if (!$found || $this->flagNoStop) {
            foreach ($patterns as $pattern => $comment) {
                //Call the function that is named in $scanFunction
                //This allows multiple search/match functions to be used without duplicating the loop code.
                $position = $this->$scanFunction($pattern, $fileContent);

                if ($position !== false) {
                    $found = true;
                    $lineNumber = 0;

                    if ($this->flagLineNumber) {
                        if ($pos = strrpos(substr((string) $fileContent, 0, $position), "\n")) {
                            $lineNumber = substr_count(substr((string) $fileContent, 0, $pos + 1), "\n") + 1;
                        }
                    }

                    $this->printPath($found, $path, $pattern, $comment, $hash, $lineNumber);

                    if (!$this->flagNoStop) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * @see https://www.mkwd.net/binary-search-algorithm-in-php/
     *
     * @param string $needle
     * @param array $haystack
     * @param mixed $high
     * @param int $low
     * 
     * @return mixed
     */
    protected function binarySearch(string $needle, array $haystack, mixed $high, int $low = 0): mixed
    {
        $key = false;
        // Whilst we have a range. If not, then that match was not found.
        while ($high >= $low) {
            // Find the middle of the range.
            $mid = (int)floor(($high + $low) / 2);
            // Compare the middle of the range with the needle. This should return <0 if it's in the first part of the range,
            // or >0 if it's in the second part of the range. It will return 0 if there is a match.
            $cmp = strcmp($needle, (string) $haystack[$mid]);
            // Adjust the range based on the above logic, so the next loop iteration will use the narrowed range
            if ($cmp < 0) {
                $high = $mid - 1;
            } elseif ($cmp > 0) {
                $low = $mid + 1;
            } else {
                $key = $mid;
                break;
            }
        }

        return $key;
    }

    /**
     * Loads target file contents for scanning
     * Initiates the multiple scan types by calling the scanLoop function
     *
     * @param string $path
     * 
     * @return bool
     */
    public function scan(string $path): bool
    {
        $this->stat['files_scanned']++;
        $fileContent = file_get_contents($path);
        $found = false;
        $inWhitelist = false;
        $hash = md5($fileContent);
        $toSearch = '';
        $comment = '';

        if ($this->inWhitelist($hash)) {
            $inWhitelist = true;
        } elseif (!$this->flagBase64) {
            $this->scanLoop('scanFunc_STR', $fileContent, $this->patterns_raw, $path, $found, $hash);
            $this->scanLoop('scanFunc_STRI', $fileContent, $this->patterns_iraw, $path, $found, $hash);
            $this->scanLoop('scanFunc_RE', $fileContent, $this->patterns_re, $path, $found, $hash);
        } else {
            $this->scanLoop('scanFunc_STR', $fileContent, $this->patterns_b64functions, $path, $found, $hash);
            $this->scanLoop('scanFunc_STR', $fileContent, $this->patterns_b64keywords, $path, $found, $hash);
        }

        if (!$found) {
            $this->printPath($found, $path, $toSearch, $comment, $hash, 0, $inWhitelist);
            return false;
        }

        $this->stat['files_infected']++;
        return true;
    }
}
