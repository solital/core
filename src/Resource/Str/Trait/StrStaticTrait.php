<?php

namespace Solital\Core\Resource\Str\Trait;

use Symfony\Component\Uid\Uuid;
use Solital\Core\Resource\Str\Exceptions\InvalidStringArgumentException;

trait StrStaticTrait
{
    /**
     * Returns everything in a source string that exists between the first occurance of each of the two key substrings
     * [!!] Not chainable
     *
     * @param string $string
     * @param string $start
     * @param string $end
     * 
     * @return string
     */
    public static function between(string $string, string $start, string $end): string
    {
        $string = ' ' . $string;
        $ini = mb_strpos($string, $start, 0, 'UTF-8');
        if ($ini == 0) return '';
        $ini += mb_strlen($start, 'UTF-8');
        $len = mb_strpos($string, $end, $ini, 'UTF-8') - $ini;
        return mb_substr($string, $ini, $len, 'UTF-8');
    }

    /**
     * Does current string contain a subtring?
     * [!!] Not chainable
     *
     * @param string $haystack
     * @param string $needle
     * 
     * @return bool 
     */
    public static function contains(string $haystack, string $needle): bool
    {
        if (self::isBinary($haystack)) return mb_str_contains($haystack, $needle);
        return str_contains($haystack, $needle);
    }

    /**
     * Return information about characters used in a string
     * 
     * @param string $string   The string to be examined
     * @param int    $mode     Specifies what information to return.
     *                         - 0: Returns an array with the byte-value as key and the frequency of
     *                         every byte as value.
     *                         - 1: Same as 0 but only byte-values with a frequency greater than zero are listed.
     *                         - 2: Same as 0 but only byte-values with a frequency equal to zero are listed.
     *                         - 3: Returns a string containing all unique characters in the string.
     *                         - 4: Returns a string containing all characters in the string that are not used.
     * @param string $encoding [optional] The character encoding. Defaults to 'UTF-8'.
     *
     * @return int[]|string Returns the information requested based on the mode parameter:
     *                      - Mode 0, 1, or 2: returns an array with byte-values as keys and frequencies as values.
     *                      - Mode 3 or 4: returns a string with unique characters or unused characters.
     *
     * @throws \ValueError if the mode parameter is not between 0 and 4 (inclusive)
     */
    public static function countChars(string $string, int $mode = 0): mixed
    {
        return mb_count_chars($string, $mode);
    }

    /**
     * Binary safe string comparison
     * [!!] Not chainable
     *
     * Return values:
     *  < 0 if this string is less than target string
     *  > 0 if this string is greater than target string
     * == 0 if both strings are equal
     *
     * @link   http://php.net/manual/en/function.strcmp.php
     *
     * @param string $string1
     * @param string $string2
     * @param string|null $encoding
     * 
     * @return int
     */
    public static function compare(string $string1, string $string2, ?string $encoding = null): int
    {
        if (null === $encoding) $encoding = mb_internal_encoding();
        return strcmp(mb_strtoupper($string1, $encoding), mb_strtoupper($string2, $encoding));
    }

    /**
     * Binary safe case-insensitive string comparison
     * [!!] Not chainable
     *
     * Return values:
     * < 0 if this string is less than target string
     * > 0 if this string is greater than target string
     * == 0 if strings are equal
     *
     * @link   http://php.net/manual/en/function.strcasecmp.php
     *
     * @param  string $target to compare current string to
     *
     * @return int
     */
    public static function compareInsensitive(string $string1, string $string2, ?string $encoding = null): int
    {
        if (null === $encoding) $encoding = mb_internal_encoding();
        return strcasecmp(mb_strtoupper($string1, $encoding), mb_strtoupper($string2, $encoding));
    }

    /**
     * Does current string not contain a subtring?
     * [!!] Not chainable
     *
     * @param string $haystack
     * @param string $needle
     * 
     * @return bool
     */
    public static function doesntContain(string $haystack, string $needle): bool
    {
        return !self::contains($haystack, $needle);
    }

    /**
     * Checks if haystack ends with needle
     * 
     * @param string $haystack
     * @param string $needle
     * @param bool $caseInsensitive
     * @param string $encoding
     * 
     * @return bool
     */
    public static function endsWith(
        string $haystack,
        string $needle,
        bool $caseInsensitive = false,
        string $encoding = 'UTF-8'
    ): bool {
        $validNeedle = self::checkString($needle);
        if (0 === mb_strlen($validNeedle, $encoding)) return true;
        $haystackEnd = mb_substr($haystack, mb_strlen($validNeedle, $encoding) * -1, encoding: $encoding);
        if ($caseInsensitive) return 0 === mb_stripos($haystackEnd, $validNeedle, 0, $encoding);
        return 0 === mb_strpos($haystackEnd, $validNeedle, 0, $encoding);
    }

    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string|array $needle
     * @param int $offset
     * @param string|null $encoding
     * 
     * @return mixed
     */
    public static function position(
        string $haystack,
        string|array $needles,
        int $offset = 0,
        ?string $encoding = null
    ): mixed {
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = self::position($haystack, $str);
                } else {
                    (self::isBinary($haystack)) ?
                        $pos = mb_strpos($haystack, $str, $offset, $encoding) :
                        $pos = strpos($haystack, $str, $offset);
                }

                if ($pos !== FALSE) return $pos;
            }
        }

        if (self::isBinary($haystack)) return mb_strpos($haystack, $needles, $offset, $encoding);
        return strpos($haystack, $needles);
    }

    /**
     * Checks if haystack begins with needle
     * 
     * @param string $haystack
     * @param string $needle
     * @param bool $caseInsensitive
     * @param string $encoding
     * 
     * @return bool
     */
    public static function startsWith(
        string $haystack,
        string $needle,
        bool $caseInsensitive = false,
        string $encoding = 'UTF-8'
    ): bool {
        $validNeedle = self::checkString($needle);
        if (0 === mb_strlen($validNeedle, $encoding)) return true;
        if ($caseInsensitive) return 0 === mb_stripos($haystack, $validNeedle, 0, $encoding);
        return 0 === mb_strpos($haystack, $validNeedle, 0, $encoding);
    }

    /**
     * Returns a string containing all unique characters (in current string)
     *
     * @link   http://php.net/count_chars
     *
     * @return string
     */
    public static function uniqueChars(string $string): string
    {
        if (self::isBinary($string)) return mb_count_chars($string, 3);
        return count_chars($string, 3);
    }

    /**
     * Returns the identifier as a raw binary string
     *
     * @return string
     */
    public static function uuid(): string
    {
        return Uuid::v4()->toBinary();
    }

    /**
     * Counts the number of words inside string.
     * [!!] Not chainable
     *
     * @param  string $charlist Optional list of additional characters which will be considered as 'word'
     *
     * @return array|int
     */
    public static function wordCount(string $string, ?string $charlist = null): array|int
    {
        if (self::isBinary($string)) return mb_str_word_count($string, 0, $charlist);
        return str_word_count($string, 0, $charlist);
    }

    /**
     * Returns the list of words inside string
     * [!!] Not chainable
     *
     * @param  string $charlist Optional list of additional characters which will be considered as 'word'
     *
     * @return array   Array in [position => word] format
     */
    public static function words(string $string, ?string $charlist = null): array
    {
        if (self::isBinary($string)) return mb_str_word_count($string, 2, $charlist);
        return str_word_count($string, 2, $charlist);
    }

    /**
     * @param string $str
     * 
     * @return bool
     */
    protected static function isBinary(string $str): bool
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
    }

    /**
     * @param mixed $value
     * 
     * @return string
     */
    protected static function checkString(mixed $value): string
    {
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) return strval($value);
        throw new InvalidStringArgumentException();
    }
}
