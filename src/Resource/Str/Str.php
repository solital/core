<?php

namespace Solital\Core\Resource\Str;

use Solital\Core\Resource\Str\Exceptions\StrException;
use Solital\Core\Resource\Str\Trait\StrStaticTrait;

final class Str extends StrMask
{
    use StrStaticTrait;

    const DEFAULT_CHUNK_END = "\r\n";
    const DEFAULT_CHUNK_LENGTH = 76;
    const DEFAULT_PAD_STRING = ' ';
    const DEFAULT_PAD_TYPE = \STR_PAD_RIGHT;
    const DEFAULT_TRIM_CHARACTER_MASK = " \t\n\r\0\x0B";

    /**
     * String encoding
     *
     * @var string
     */
    protected string $encoding;

    /**
     * Contains the list of values that have been changed in this object
     *
     * @var array
     */
    protected array $values = [];

    /**
     * Creates a Str object
     *
     * @param string $string to represent
     */
    public function __construct(string $string)
    {
        $this->set($string);
    }

    /**
     * What happens when non-existing method is called on this object
     * e.g. one that's not defined or is protected
     *
     * @param  string $method
     * @param  array  $arguments
     *
     * @return self   (chainable)
     */
    final public function __call($method, array $arguments)
    {
        throw new \BadMethodCallException(sprintf('Invalid method "%s" called', $method));
    }

    /**
     * What happenes when this object is casted to string
     *
     * @return string
     */
    final public function __toString()
    {
        return $this->value();
    }

    /**
     * Returns the current value of this string
     *
     * @return string
     */
    final public function value(): string
    {
        end($this->values);
        return current($this->values);
    }

    /**
     * Gets the current string encoding
     *
     * @return string       Encoding
     * @throws StrException If encoding can't be detected
     */
    final public function encoding(): string
    {
        if (!isset($this->encoding)) {
            if (function_exists('mb_detect_encoding')) {
                return $this->encoding = mb_detect_encoding($this->value());
            }

            throw new StrException(sprintf('Could not detect string encoding : %s', $this->value()));
        }

        return $this->encoding;
    }

    /**
     * Returns the list of all values this object used to have
     *
     * @return string[]
     */
    final public function values(): mixed
    {
        return $this->values;
    }

    /**
     * Quote string with slashes in a C style
     *
     * @link   http://php.net/manual/en/function.addcslashes.php
     *
     * @param  string $charlist
     *
     * @return self (chainable)
     */
    public function addCslashes(string $charlist): self
    {
        return $this->set(addcslashes($this->value(), $charlist));
    }

    /**
     * Quote string with slashes
     *
     * @link   http://php.net/manual/en/function.addslashes.php
     * @return self (chainable)
     */
    public function addSlashes(): self
    {
        return $this->set(addslashes($this->value()));
    }

    /**
     * Get the string after the first occurence of the substring.
     * If the substring is not found, an empty string is returned.
     *
     * @param string $substr
     * 
     * @return self (chainable)
     */
    public function after(string $substr): self
    {
        if (self::isBinary($this->value())) {
            $pos = mb_strpos($this->value(), $substr, encoding: 'UTF-8');
        } else {
            $pos = strpos($this->value(), $substr);
        }

        if ($pos === false) {
            $string = $this->value();
        } else {
            $string = substr($this->value(), $pos + strlen($substr));
        }

        return $this->set($string);
    }

    /**
     * Get the string before the first occurence of the substring.
     * If the substring is not found, the whole string is returned.
     *
     * @param string $substr
     * 
     * @return self (chainable)
     */
    public function before(string $substr): self
    {
        if (self::isBinary($this->value())) {
            $pos = mb_strpos($this->value(), $substr, encoding: 'UTF-8');
        } else {
            $pos = strpos($this->value(), $substr);
        }

        if ($pos === false) {
            $string =  $this->value();
        } else {
            $string = substr($this->value(), 0, $pos);
        }

        return $this->set($string);
    }

    /**
     * Splits the string into smaller chunks
     *
     * @link   http://php.net/chunk_split
     *
     * @param  int    $length The chunk length.
     * @param  string $end The line ending sequence.
     *
     * @return self             (chainable)
     */
    public function chunkSplit(?int $length = null, ?string $end = null): self
    {
        $end = $this->default($end, static::DEFAULT_CHUNK_END);
        $length = $this->default($length, static::DEFAULT_CHUNK_LENGTH);

        if (self::isBinary($this->value())) {
            $chunk = mb_chunk_split($this->value(), $length, $end);
        } else {
            $chunk = chunk_split($this->value(), $length, $end);
        }

        return $this->set($chunk);
    }

    /**
     * Appends another string to this string
     *
     * @param  string $string String to concatonate
     *
     * @return self           Chainable
     */
    public function concat(string $string): self
    {
        return $this->set($this->value() . $string);
    }

    /**
     * Case-insensitive version of Str::replace()
     *
     * @link   http://php.net/str_ireplace
     *
     * @param  array $replacements List of search -> replace pairs
     *
     * @return self (chainable)
     * @see    Str::replace()
     */
    public function ireplace(array $replacements): self
    {
        return $this->set(
            str_ireplace(array_keys($replacements), array_values($replacements), $this->value())
        );
    }

    /**
     * Strip whitespace (or other characters) from the beginning of a string
     *
     * @link   http://php.net/ltrim
     *
     * @param  string $character_mask List of characters to trim (optional)
     *
     * @return self                   Chainable
     */
    public function ltrim(?string $character_mask = null): self
    {
        $character_mask = $this->default($character_mask, static::DEFAULT_TRIM_CHARACTER_MASK);
        $character_mask = $this->formatCharacterMask($character_mask);

        if (self::isBinary($this->value())) {
            $string = mb_ltrim($this->value(), $character_mask);
        } else {
            $string = ltrim($this->value(), $character_mask);
        }

        return $this->set($string);
    }

    /**
     * Converts new lines to <br /> elements
     *
     * @return self Chainable
     */
    public function nl2br(): self
    {
        return $this->set(nl2br($this->value()));
    }

    /**
     * Pad the string to a certain length with another string
     *
     * @link   http://php.net/str_pad
     *
     * @param  int    $pad_length If the value of pad_length is negative, less than, or equal to the length of current string, no padding takes place
     * @param  string $pad_string String to pad the current string with
     * @param  int    $pad_type Optional, can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH. If pad_type is not specified it is assumed to be STR_PAD_RIGHT
     *
     * @return self               Chainable
     */
    public function pad(int $pad_length, ?string $pad_string = null, ?int $pad_type = null): self
    {
        $pad_string = $this->default($pad_string, static::DEFAULT_PAD_STRING);
        $pad_type = $this->default($pad_type, static::DEFAULT_PAD_TYPE);

        if (self::isBinary($this->value())) {
            $pad = mb_str_pad($this->value(), $pad_length, $pad_string, $pad_type);
        } else {
            $pad = str_pad($this->value(), $pad_length, $pad_string, $pad_type);
        }

        return $this->set($pad);
    }

    /**
     * Replace characters with accents with normal characters.
     *
     * @return self
     */
    public function removeAccents(): self
    {
        $from = [
            'À',
            'Á',
            'Â',
            'Ã',
            'Ä',
            'Å',
            'Æ',
            'Ç',
            'È',
            'É',
            'Ê',
            'Ë',
            'Ì',
            'Í',
            'Î',
            'Ï',
            'Ð',
            'Ñ',
            'Ò',
            'Ó',
            'Ô',
            'Õ',
            'Ö',
            'Ø',
            'Ù',
            'Ú',
            'Û',
            'Ü',
            'Ý',
            'ß',
            'à',
            'á',
            'â',
            'ã',
            'ä',
            'å',
            'æ',
            'ç',
            'è',
            'é',
            'ê',
            'ë',
            'ì',
            'í',
            'î',
            'ï',
            'ñ',
            'ò',
            'ó',
            'ô',
            'õ',
            'ö',
            'ø',
            'ù',
            'ú',
            'û',
            'ü',
            'ý',
            'ÿ',
            'Ā',
            'ā',
            'Ă',
            'ă',
            'Ą',
            'ą',
            'Ć',
            'ć',
            'Ĉ',
            'ĉ',
            'Ċ',
            'ċ',
            'Č',
            'č',
            'Ď',
            'ď',
            'Đ',
            'đ',
            'Ē',
            'ē',
            'Ĕ',
            'ĕ',
            'Ė',
            'ė',
            'Ę',
            'ę',
            'Ě',
            'ě',
            'Ĝ',
            'ĝ',
            'Ğ',
            'ğ',
            'Ġ',
            'ġ',
            'Ģ',
            'ģ',
            'Ĥ',
            'ĥ',
            'Ħ',
            'ħ',
            'Ĩ',
            'ĩ',
            'Ī',
            'ī',
            'Ĭ',
            'ĭ',
            'Į',
            'į',
            'İ',
            'ı',
            'Ĳ',
            'ĳ',
            'Ĵ',
            'ĵ',
            'Ķ',
            'ķ',
            'Ĺ',
            'ĺ',
            'Ļ',
            'ļ',
            'Ľ',
            'ľ',
            'Ŀ',
            'ŀ',
            'Ł',
            'ł',
            'Ń',
            'ń',
            'Ņ',
            'ņ',
            'Ň',
            'ň',
            'ŉ',
            'Ō',
            'ō',
            'Ŏ',
            'ŏ',
            'Ő',
            'ő',
            'Œ',
            'œ',
            'Ŕ',
            'ŕ',
            'Ŗ',
            'ŗ',
            'Ř',
            'ř',
            'Ś',
            'ś',
            'Ŝ',
            'ŝ',
            'Ş',
            'ş',
            'Š',
            'š',
            'Ţ',
            'ţ',
            'Ť',
            'ť',
            'Ŧ',
            'ŧ',
            'Ũ',
            'ũ',
            'Ū',
            'ū',
            'Ŭ',
            'ŭ',
            'Ů',
            'ů',
            'Ű',
            'ű',
            'Ų',
            'ų',
            'Ŵ',
            'ŵ',
            'Ŷ',
            'ŷ',
            'Ÿ',
            'Ź',
            'ź',
            'Ż',
            'ż',
            'Ž',
            'ž',
            'ſ',
            'ƒ',
            'Ơ',
            'ơ',
            'Ư',
            'ư',
            'Ǎ',
            'ǎ',
            'Ǐ',
            'ǐ',
            'Ǒ',
            'ǒ',
            'Ǔ',
            'ǔ',
            'Ǖ',
            'ǖ',
            'Ǘ',
            'ǘ',
            'Ǚ',
            'ǚ',
            'Ǜ',
            'ǜ',
            'Ǻ',
            'ǻ',
            'Ǽ',
            'ǽ',
            'Ǿ',
            'ǿ'
        ];

        $to = [
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'AE',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'D',
            'N',
            'O',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            's',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'ae',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'n',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'A',
            'a',
            'A',
            'a',
            'A',
            'a',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'C',
            'c',
            'D',
            'd',
            'D',
            'd',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'E',
            'e',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'G',
            'g',
            'H',
            'h',
            'H',
            'h',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'I',
            'i',
            'IJ',
            'ij',
            'J',
            'j',
            'K',
            'k',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'L',
            'l',
            'l',
            'l',
            'N',
            'n',
            'N',
            'n',
            'N',
            'n',
            'n',
            'O',
            'o',
            'O',
            'o',
            'O',
            'o',
            'OE',
            'oe',
            'R',
            'r',
            'R',
            'r',
            'R',
            'r',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'S',
            's',
            'T',
            't',
            'T',
            't',
            'T',
            't',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'W',
            'w',
            'Y',
            'y',
            'Y',
            'Z',
            'z',
            'Z',
            'z',
            'Z',
            'z',
            's',
            'f',
            'O',
            'o',
            'U',
            'u',
            'A',
            'a',
            'I',
            'i',
            'O',
            'o',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'U',
            'u',
            'A',
            'a',
            'AE',
            'ae',
            'O',
            'o'
        ];

        return $this->set(str_replace($from, $to, $this->value()));
    }

    /**
     * Repeats the string
     *
     * @link   http://php.net/str_repeat
     *
     * @param  int $multiplier How many times to repeat the string
     *
     * @return self               Chainable
     */
    public function repeat(int $multiplier): self
    {
        return $this->set(str_repeat($this->value(), $multiplier));
    }

    /**
     * Replaces the occurences of keys with their values
     *
     * @link   http://php.net/str_replace
     *
     * @param  array $replacements List of [search => replace] pairs
     * @param  int   $count If passed, this will be set to the number of replacements performed.
     *
     * @return self                 Chainable
     */
    public function replace(array $replacements, &$count = null): self
    {
        return $this->set(
            str_replace(array_keys($replacements), array_values($replacements), $this->value(), $count)
        );
    }

    /**
     * Reverses current string
     *
     * @param string $encoding
     * 
     * @link   http://php.net/manual/en/function.strrev.php
     * @return self Chainable
     */
    public function reverse(string $encoding = 'UTF-8'): self
    {
        if (self::isBinary($this->value())) {
            $rev = mb_strrev($this->value(), $encoding);
        } else {
            $rev = strrev($this->value());
        }

        return $this->set($rev);
    }

    /**
     * Perform the ROT13 transform on current string
     *
     * The ROT13 encoding simply shifts every letter
     * by 13 places in the alphabet while leaving non-alpha
     * characters untouched. Encoding and decoding are done by the
     * same function, passing an encoded string as argument will
     * return the original version.
     *
     * @link   http://php.net/str_rot13
     * @return self              Chainable
     */
    public function rot13(): self
    {
        return $this->set(str_rot13($this->value()));
    }

    /**
     * Strip whitespace (or other characters) from the end
     *
     * @param mixed $character_mask List of characters to trim (optional)
     *
     * @return self                   Chainable
     */
    public function rtrim(mixed $character_mask = null): self
    {
        $character_mask = $this->default($character_mask, static::DEFAULT_TRIM_CHARACTER_MASK);
        $character_mask = $this->formatCharacterMask($character_mask);

        if (self::isBinary($this->value())) {
            $string = mb_rtrim($this->value(), $character_mask);
        } else {
            $string = rtrim($this->value(), $character_mask);
        }

        return $this->set($string);
    }

    /**
     * Randomly shuffles the string
     *
     * @return self Chainable
     */
    public function shuffle(): self
    {
        if (self::isBinary($this->value())) {
            $shuffle = mb_str_shuffle($this->value());
        } else {
            $shuffle = str_shuffle($this->value());
        }

        return $this->set($shuffle);
    }

    /**
     * Truncate String (shorten) with or without ellipsis.
     *
     * @param int    $maxLength   Maximum length of string
     * @param bool   $add_ellipsis if True, "..." is added in the end of the string, default true
     * @param bool   $wordsafe    if True, Words will not be cut in the middle
     *
     * @return self Chainable
     */
    public function shorten(int $max_length, bool $add_ellipsis = true, bool $wordsafe = false): self
    {
        $ellipsis = '';
        $max_length = max($max_length, 0);

        /* if (mb_strlen($this->value()) <= $max_length) {
            return $this->value();
        } */

        if ($add_ellipsis === true) {
            $ellipsis = mb_substr('...', 0, $max_length);
            $max_length -= mb_strlen($ellipsis);
            $max_length = max($max_length, 0);
        }

        $string = mb_substr($this->value(), 0, $max_length);

        if ($wordsafe) {
            $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $max_length));
        }

        if ($add_ellipsis) {
            $string .= $ellipsis;
        }

        return $this->set($string);
    }

    /**
     * Generate a URL friendly slug from the given string.
     *
     * @param string $string
     * @param string $glue
     * @return string
     */
    public function slug(string $glue = '-'): string
    {
        $normalized = self::removeAccents();
        $lower = strtolower($normalized);

        $string = preg_replace('/[\W_]+/', $glue, $lower);
        return $this->set($string);
    }

    /**
     * Convert special characters to HTML entities
     * 
     * @param bool $decode
     * 
     * @return self
     */
    public function specialchars(bool $decode = false): self
    {
        if ($decode == true) {
            return $this->set(htmlspecialchars_decode($this->value()));
        }

        return $this->set(htmlspecialchars($this->value()));
    }

    /**
     * Strip HTML and PHP tags
     *
     * @link   http://php.net/manual/en/function.strip-tags.php
     *
     * @param  string $allowable_tags List of allowed tags
     *
     * @return self                    Chainable
     */
    public function stripTags(?string $allowable_tags = null): self
    {
        return $this->set(strip_tags($this->value(), $allowable_tags));
    }

    /**
     * Translate characters or replace substrings
     *
     * @link   http://php.net/strtr
     *
     * @param  array $replace_pairs Array in the form array('from' => 'to', ...)
     *
     * @return self                  Chainable
     */
    public function translate(array $replace_pairs): self
    {
        return $this->set(strtr($this->value(), $replace_pairs));
    }

    /**
     * Strips whitespace (or other characters) from the beginning and end of the string
     *
     * @link   http://php.net/manual/en/function.trim.php
     *
     * @param mixed $character_mask List of characters to trim (optional)
     *
     * @return self                     Chainable
     */
    public function trim(mixed $character_mask = null): self
    {
        $character_mask = $this->default($character_mask, static::DEFAULT_TRIM_CHARACTER_MASK);
        $character_mask = $this->formatCharacterMask($character_mask);

        if (self::isBinary($this->value())) {
            $string = mb_trim($this->value(), $character_mask);
        } else {
            $string = trim($this->value(), $character_mask);
        }

        return $this->set($string);
    }

    /**
     * Make a string uppercase.
     *
     * @return self
     */
    public function toUpper(): self
    {
        if (self::isBinary($this->value())) {
            $string = mb_strtoupper($this->value());
        } else {
            $string = strtoupper($this->value());
        }

        return $this->set($string);
    }

    /**
     * Make a string lowercase.
     *
     * @return self
     */
    public function toLower(): self
    {
        if (self::isBinary($this->value())) {
            $string = mb_strtolower($this->value());
        } else {
            $string = strtolower($this->value());
        }

        return $this->set($string);
    }

    /**
     * Undoes the last $steps operations
     *
     * @param  int $steps How many steps to undo
     *
     * @return self           Chainable
     * @throws \TypeError if steps isn't an integer
     */
    public function undo(int $steps = 1): self
    {
        if (!is_int($steps)) {
            throw new \TypeError('Str::undo() must be given an integer');
        }

        $steps = min($steps, count($this->values) - 1);

        for ($i = 1; $i <= $steps; $i++) {
            array_pop($this->values);
        }

        return $this;
    }

    // ----- Mask method will be here and not in `StrStaticTrait`

    /**
     * Entry point of Mask library
     * 
     * @param string $inputValue      Value to apply mask
     * @param string $maskExpression  Mask for output
     * @param null|array $config      Config of operation
     * 
     * @return mixed
     */
    public static function mask(string $inputValue, string $maskExpression, ?array $config = null): mixed
    {
        return self::apply($inputValue, $maskExpression, $config);
    }

    /**
     * Used for setting default parameter values.
     *
     * If the $passed value is NULL, this will return the $default
     * otherwise $passed will be returned back.
     *
     * @param  mixed $passed
     * @param  mixed $default
     *
     * @return mixed
     */
    protected function default(mixed $passed, mixed $default): mixed
    {
        return $passed === null ? $default : $passed;
    }

    /**
     * Allows using array character masks in trim methods
     *
     * @param  string|array $character_mask Character mask to format (if array)
     *
     * @return string                       Character mask usable by trim functions
     */
    protected function formatCharacterMask(mixed $character_mask): string
    {
        if (is_array($character_mask)) {
            return \implode('', $character_mask);
        }

        return $character_mask;
    }

    /**
     * Sets a new value for current string
     *
     * @param  string $value String value to set
     *
     * @return self          Chainable
     */
    protected function set(string $value): self
    {
        $this->values[] = $value;

        // Reset encoding
        unset($this->encoding);
        return $this;
    }
}
