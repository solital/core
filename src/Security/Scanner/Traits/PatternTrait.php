<?php

namespace Solital\Core\Security\Scanner\Traits;

trait PatternTrait
{
    /**
     * Pattern File Attributes
     *
     * @var array
     */
    protected array $patterns_raw = [];
    protected array $patterns_iraw = [];
    protected array $patterns_re = [];
    protected array $patterns_b64functions = [];
    protected array $patterns_b64keywords = [];

    /**
     * Handles pattern loading and saving to the class object
     *
     * @return void
     */
    public function initializePatterns(): void
    {
        $dir = dirname(__FILE__, 2);

        //Loads either the primary scanning patterns or the base64 patterns depending on -b/--base64 flag
        if (!$this->flagBase64) {
            $this->patterns_raw = $this->loadPatterns($dir . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'patterns_raw.txt');
            $this->patterns_iraw = $this->loadPatterns($dir . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'patterns_iraw.txt');
            $this->patterns_re = $this->loadPatterns($dir . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'patterns_re.txt');
        } else {
            $this->patterns_b64functions = $this->loadPatterns($dir . DIRECTORY_SEPARATOR . 'base64_patterns' . DIRECTORY_SEPARATOR . 'php_functions.txt');
            $this->patterns_b64keywords = $this->loadPatterns($dir . DIRECTORY_SEPARATOR . 'base64_patterns' . DIRECTORY_SEPARATOR . 'php_keywords.txt');
        }

        //Adds additional checks to patterns_raw
        //This may be something to move into a pattern file rather than leave hardcoded.
        if ($this->flagExtraCheck) {
            $this->patterns_raw['googleBot'] = '# ';
            $this->patterns_raw['htaccess'] = '# ';
        }
    }

    /**
     * Loads individual pattern files
     * Skips blank linese
     * Stores most recent comment with the pattern in the list[] array
     * Returns an array of patterns:comments in key:value pairs
     *
     * @param string $file
     * 
     * @return array 
     */
    protected function loadPatterns(string $file): array
    {
        $last_comment = '';
        $list = [];

        if (is_readable($file)) {
            foreach (file($file) as $pattern) {
                //Check if the line is only whitespace and skips.
                if (strlen(trim($pattern)) == 0) {
                    continue;
                }

                //Check if first char in pattern is a '#' which indicates a comment and skips.
                //Stores the comment to be stored with the pattern in the list as key:value pairs.
                //The pattern is the key and the comment is the value.
                if ($pattern[0] === '#') {
                    $last_comment = $pattern;
                    continue;
                }

                $list[trim($pattern)] = trim($last_comment);
            }
        }

        return $list;
    }

    /**
     * @see http://stackoverflow.com/a/13914119
     *
     * @param string $path
     * @param string $pattern
     * @param bool $ignoreCase
     * 
     * @return bool
     */
    protected function pathMatches(string $path, string $pattern, bool $ignoreCase = false): bool
    {
        /* $expr = preg_replace_callback(
            '/[\\\\^$.[\\]|()?*+{}\\-\\/]/',
            function ($matches) {
                switch ($matches[0]) {
                    case '*':
                        return '.*';
                    case '?':
                        return '.';
                    default:
                        return '\\' . $matches[0];
                }
            },
            $pattern
        ); */

        $expr = preg_replace_callback(
            '/[\\\\^$.[\\]|()?*+{}\\-\\/]/',
            fn ($matches) => match ($matches[0]) {
                '*' => '.*',
                '?' => '.',
                default => '\\' . $matches[0]
            },
            $pattern
        );

        $expr = '/' . $expr . '/';

        if ($ignoreCase) {
            $expr .= 'i';
        }

        return (bool)preg_match($expr, $path);
    }
}
