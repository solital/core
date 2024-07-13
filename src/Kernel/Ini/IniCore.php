<?php

namespace Solital\Core\Kernel\Ini;

final class IniCore
{
    /**
     * This sets the maximum amount of memory in bytes that a script is allowed to allocate.
     * Default is 128M
     *
     * @param string $memory_limit
     * 
     * @return self
     */
    public function setMemoryLimit(string $memory_limit): self
    {
        $limit = $this->human2byte($memory_limit);
        ini_set("memory_limit", $limit);
        return $this;
    }

    /**
     * "UTF-8" is the default value and its value is used as the default character encoding
     * Setting default_charset to an empty value is not recommended.
     * 
     * @param string $charset
     * 
     * @return self
     */
    public function setDefaultCharset(string $charset): self
    {
        ini_set("default_charset", $charset);
        return $this;
    }

    /**
     * This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser.
     *
     * @param int $time
     * 
     * @return self
     */
    public function setMaxExecutionTime(int $time): self
    {
        ini_set("max_execution_time", $time);
        set_time_limit($time);
        return $this;
    }

    /**
     * Sets the default timezone used by all date/time functions in a script
     *
     * @param string $timezone
     * 
     * @return self
     */
    public function setDefaultTimestamp(string $timezone): self
    {
        date_default_timezone_set($timezone);
        return $this;
    }

    /**
     * The default national language setting (NLS) used in mbstring
     * Default is "neutral"
     *
     * @param string $language
     * 
     * @return self
     */
    public function setMbstringLanguage(string $language): self
    {
        ini_set("mbstring.language", $language);
        return $this;
    }

    /**
     * Converts a human readable file size value to a number of bytes that it
     * represents. Supports the following modifiers: K, M, G and T.
     * Invalid input is returned unchanged.
     *
     * Example:
     * <code>
     * $config->human2byte(10);          // 10
     * $config->human2byte('10b');       // 10
     * $config->human2byte('10k');       // 10240
     * $config->human2byte('10K');       // 10240
     * $config->human2byte('10kb');      // 10240
     * $config->human2byte('10Kb');      // 10240
     * // and even
     * $config->human2byte('   10 KB '); // 10240
     * </code>
     *
     * @param string|int $value
     * @return string
     */
    private function human2byte(string|int $value): string
    {
        return preg_replace_callback('/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i', function ($m) {
            switch (strtolower($m[2])) {
                case 't':
                    $m[1] *= 1024;
                case 'g':
                    $m[1] *= 1024;
                case 'm':
                    $m[1] *= 1024;
                case 'k':
                    $m[1] *= 1024;
            }
            return $m[1];
        }, $value);
    }
}
