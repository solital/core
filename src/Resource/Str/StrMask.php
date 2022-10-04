<?php

namespace Solital\Core\Resource\Str;

class StrMask
{
    /**
     * List of available patterns
     * 
     * @var array
     */
    private static array $maskAvailablePatterns = [
        '0' => [
            'pattern' => "/\d/"
        ],
        '9' => [
            'pattern' => "/\d/",
            'optional' => true
        ],
        'X' => [
            'pattern' => "/\d/",
            'symbol' => '*'
        ],
        'A' => [
            'pattern' => "/[a-zA-Z0-9]/"
        ],
        'S' => [
            'pattern' => "/[a-zA-Z]/"
        ],
        'd' => [
            'pattern' => "/\d/"
        ],
        'm' => [
            'pattern' => "/\d/"
        ],
        'M' => [
            'pattern' => "/\d/"
        ],
        'H' => [
            'pattern' => "/\d/"
        ],
        'h' => [
            'pattern' => "/\d/"
        ],
        's' => [
            'pattern' => "/\d/"
        ],
    ];

    /**
     * List of special characters
     * 
     * @var array
     */
    private static array $maskSpecialCharacters = ['-', '/', '(', ')', '.', ':', ' ', '+', ',', '@', '[', ']', '"', "'"];

    /**
     * Entry point of Mask library
     * 
     * @param string $inputValue      Value to apply mask
     * @param string $maskExpression  Mask for output
     * @param null|array $config      Config of operation
     * 
     * @return bool|string
     */
    public static function apply(string $inputValue, string $maskExpression, ?array $config = null): mixed
    {
        if (!$inputValue || !$maskExpression) {
            return '';
        }

        if ($config === null) {
            $config = array();
        }

        $cursor = 0;
        $result = '';
        $multi = false;
        // TODO: Implement shift

        $prefix = isset($config["prefix"]) ? $config["prefix"] : "";
        $suffix = isset($config["suffix"]) ? $config["suffix"] : "";

        // Remove prefix from input value
        if ((substr($inputValue, 0, strlen($prefix)) == $prefix) && (strlen($prefix) > 0)) {
            $inputValue = substr($inputValue, strlen($prefix));
        }

        if (self::startsWith($maskExpression, 'percent')) {
            $inputValue = self::checkInput($inputValue);
            $precision = self::getPrecision($maskExpression);
            $inputValue = self::checkInputPrecision($inputValue, $precision, '.');
            if (intval($inputValue) >= 0 && intval($inputValue) <= 100) {
                $result = $inputValue;
            } else {
                $result = substr($inputValue, 0, strlen($inputValue) - 1);
            }
        } elseif (
            self::startsWith($maskExpression, 'separator') ||
            self::startsWith($maskExpression, 'dot_separator') ||
            self::startsWith($maskExpression, 'comma_separator')
        ) {
            // Clean input
            if (
                preg_match("/[wа-яА-Я]/", $inputValue) ||
                preg_match("/[a-z]|[A-Z]/", $inputValue) ||
                preg_match("/[-@#!$%\\^&*()_£¬'+|~=`{}\[\]:\";<>.?\/]/", $inputValue)
            ) {
                $inputValue = self::checkInput($inputValue);
            }

            if (self::startsWith($maskExpression, 'separator')) {
                if (
                    (strpos($inputValue, ',') >= 0) &&
                    self::endsWith($inputValue, ',') &&
                    strpos($inputValue, ',') !== strrpos($inputValue, ',')
                ) {
                    $inputValue = substr($inputValue, 0, strlen($inputValue) - 1);
                }
            }

            if (self::startsWith($maskExpression, 'dot_separator')) {
                if (
                    (strpos($inputValue, ',') > 0) &&
                    strpos($inputValue, ',') === strrpos($inputValue, ',')
                ) {
                    $inputValue = str_replace(",", ".", $inputValue);
                }
            }

            if (self::startsWith($maskExpression, 'comma_separator')) {
                $inputValue = strlen($inputValue) > 1 && substr($inputValue, 0, 1) === '0' && substr($inputValue, 1, 1) !== '.'
                    ? substr($inputValue, 1)
                    : $inputValue;
            }

            $precision = self::getPrecision($maskExpression);
            if ($precision === null) {
                $precision = strlen(substr($inputValue, strrpos($inputValue, ".") + 1));
            }

            $inputValue = floatval($inputValue);

            if (self::startsWith($maskExpression, 'separator')) {
                if (preg_match("/[@#!$%^&*()_+|~=`{}\[\]:.\";<>?\/]/", $inputValue)) {
                    $inputValue = substr($inputValue, 0, strlen($inputValue) - 1);
                }
                $result = number_format($inputValue, $precision, ",", " ");
            } elseif (self::startsWith($maskExpression, 'dot_separator')) {
                if (preg_match("/[@#!$%^&*()_+|~=`{}\[\]:\s\";<>?\/]/", $inputValue)) {
                    $inputValue = substr($inputValue, 0, strlen($inputValue) - 1);
                }
                $result = number_format($inputValue, $precision, ",", ".");
            } elseif (self::startsWith($maskExpression, 'comma_separator')) {
                $result = number_format($inputValue, $precision, ".", ",");
            }

            // TODO: Implement line 139 to 160 ?

        } else {

            for ($i = 0, $inputSymbol = substr($inputValue, 0, 1); $i < strlen($inputValue); $i++, $inputSymbol = substr($inputValue, $i, 1)) {
                if ($cursor === strlen($maskExpression)) {
                    break;
                }

                $maskCursor = substr($maskExpression, $cursor, 1);
                $maskCursorP1 = substr($maskExpression, $cursor + 1, 1);
                $maskCursorP2 = substr($maskExpression, $cursor + 1, 1);
                $maskCursorM1 = ($cursor > 0) ? substr($maskExpression, $cursor - 1, 1) : "";

                //        DEBUG
                //        var_dump("ME=".$maskCursor.", ME+1=".$maskCursorP1.", ME+2=".$maskCursorP2.", ME-1=".$maskCursorM1.", IS=".$inputSymbol.", Cur=".$cursor.", IV=".$inputValue);

                // 171
                if (self::checkSymbolMask($inputSymbol, $maskCursor) && ($maskCursorP1 === '?')) {
                    $result .= $inputSymbol;
                    $cursor += 2;
                } elseif ($maskCursorP1 === '*' && $multi && self::checkSymbolMask($inputSymbol, $maskCursor)) {
                    $result .= $inputSymbol;
                    $cursor += 3;
                    $multi = false;
                    //182
                } elseif (self::checkSymbolMask($inputSymbol, $maskCursor) && ($maskCursorP1 === '*')) {
                    $result .= $inputSymbol;
                    $multi = true;
                } elseif (($maskCursorP1 === '?') && self::checkSymbolMask($inputSymbol, $maskCursorP2)) {
                    $result .= $inputSymbol;
                    $cursor += 3;
                    // 194
                } elseif (self::checkSymbolMask($inputSymbol, $maskCursor)) {
                    if ($maskCursor === 'H') {
                        if (intval($inputSymbol) > 2) {
                            $cursor += 1;
                            $i--;
                            continue;
                        }
                    }
                    // 211
                    if ($maskCursor === 'h') {
                        if ($result === '2' && intval($inputSymbol) > 3) {
                            continue;
                        }
                    }
                    if ($maskCursor === 'm') {
                        if (intval($inputSymbol) > 5) {
                            $cursor += 1;
                            $i--;
                            continue;
                        }
                    }
                    if ($maskCursor === 's') {
                        if (intval($inputSymbol) > 5) {
                            $cursor += 1;
                            $i--;
                            continue;
                        }
                    }
                    if ($maskCursor === 'd') {
                        if (intval($inputSymbol) > 3) {
                            $cursor += 1;
                            $i--;
                            continue;
                        }
                    }
                    if ($maskCursorM1 === 'd') {
                        if (intval(substr($inputValue, $cursor - 1, 2)) > 0) {
                            continue;
                        }
                    }
                    if ($maskCursor === 'M') {
                        if (intval($inputSymbol) > 1) {
                            $cursor += 1;
                            $i--;
                            continue;
                        }
                    }
                    if ($maskCursorM1 === 'M') {
                        if (intval(substr($inputValue, $cursor - 1, 2)) > 12) {
                            continue;
                        }
                    }
                    $result .= $inputSymbol;
                    $cursor++;
                    // 272
                } elseif (self::findSpecialChar($maskCursor)) {
                    $result .= $maskCursor;
                    $cursor++;
                    $i--;
                } elseif (
                    self::findSpecialChar($inputSymbol) &&
                    isset(self::$maskAvailablePatterns[$maskCursor]) &&
                    isset(self::$maskAvailablePatterns[$maskCursor]["optional"]) &&
                    self::$maskAvailablePatterns[$maskCursor]["optional"] == true
                ) {
                    $cursor++;
                    $i--;
                } elseif (
                    $maskCursorP1 === '*' &&
                    self::findSpecialChar($maskCursorP2) &&
                    self::findSpecialChar($inputSymbol) === $maskCursorP2 &&
                    $multi
                ) {
                    $cursor += 3;
                    $result .= $inputSymbol;
                } elseif (
                    $maskCursorP1 === '?' &&
                    self::findSpecialChar($maskCursorP2) &&
                    self::findSpecialChar($inputSymbol) === $maskCursorP2 &&
                    $multi
                ) {
                    $cursor += 3;
                    $result .= $inputSymbol;
                }
            }
        }

        //305
        // Last char
        if (
            ((strlen($result) + 1) === strlen($maskExpression)) &&
            self::findSpecialChar(substr($maskExpression, -1))
        ) {
            $result .= substr($maskExpression, -1);
        }

        // TODO: Implement line 320
        // TODO: Implement line 324 and 323

        // Add prefix and suffix
        if ($prefix != "") {
            $result = $prefix . $result;
        }
        if ($suffix != "") {
            $result = $result . $suffix;
        }

        return $result;
    }

    /**
     * checkSymbolMask function
     * 
     * @param mixed $inputSymbol
     * @param mixed $maskSymbol
     * 
     * @return bool
     */
    private static function checkSymbolMask(mixed $inputSymbol, mixed $maskSymbol): bool
    {
        return (isset(self::$maskAvailablePatterns[$maskSymbol]) &&
            self::$maskAvailablePatterns[$maskSymbol]["pattern"] &&
            preg_match(self::$maskAvailablePatterns[$maskSymbol]["pattern"], $inputSymbol)
        );
    }

    /**
     * Check if inputSymbol is special char
     * 
     * @param mixed $inputSymbol
     * 
     * @return bool|string
     */
    private static function findSpecialChar(mixed $inputSymbol): mixed
    {
        $returnChar = false;
        foreach (self::$maskSpecialCharacters as $char) {
            if ($inputSymbol === $char) {
                $returnChar = $char;
                break;
            }
        }
        return $returnChar;
    }

    /**
     * Determine if a string $haystack starts with $needle
     * 
     * @param mixed $haystack
     * @param mixed $needle
     * 
     * @return bool
     */
    private static function startsWith(mixed $haystack, mixed $needle): bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Determine if a string $haystack ends with $needle
     * @param mixed $haystack
     * @param mixed $needle
     * 
     * @return bool
     */
    private static function endsWith(mixed $haystack, mixed $needle): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Check that input is a number as string
     * 
     * @param string $str
     * 
     * @return string
     */
    private static function checkInput(string $str): string
    {
        $strArr = str_split($str);
        $strRet = "";

        foreach ($strArr as $char) {
            if (preg_match("/\d/", $char) || ($char === '.') || ($char === ',')) {
                $strRet .= $char;
            }
        }

        return $strRet;
    }

    /**
     * Get precision of expression
     * 
     * @param string $maskExpression
     * 
     * @return int|null
     */
    private static function getPrecision(string $maskExpression): ?int
    {
        $pos = strrpos($maskExpression, ".");
        $precision = null;

        if ($pos) {
            $precision = intval(substr($maskExpression, $pos + 1));
        }

        return $precision;
    }

    /**
     * Convert a float in string format with precision
     * 
     * @param string $inputValue
     * @param null|int $precision
     * @param string $decimalMarker
     * 
     * @return string
     */
    private static function checkInputPrecision(string $inputValue, ?int $precision, string $decimalMarker = '.'): string
    {
        if ($precision !== null) {
            $pos = strrpos($inputValue, $decimalMarker);
            $decimal = "";
            if ($pos) {
                $decimal = substr($inputValue, $pos, $precision + 1);
            }
            $inputValue = substr($inputValue, 0, $pos) . ($decimal === "." ? "" : $decimal);
        }

        return $inputValue;
    }
}
