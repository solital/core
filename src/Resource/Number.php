<?php

namespace Solital\Core\Resource;

use NumberFormatter;

class Number
{
    /**
     * Simplifies large numbers by converting them into more readable values using 
     * thousands (K), millions (M), and billions (B)
     *
     * @param float $number
     * @param int|null $decimals
     * 
     * @return float
     */
    public static function reduce(float $number, ?int $decimals = null): float
    {
        return (!is_null($decimals) && $decimals > 0) ?
            self::numberFormat($number, $decimals) :
            self::numberFormat($number, 2);
    }

    /**
     * Formats a numerical value into a specific currency format, supporting different locales and 
     * currency symbols (USD, EUR, BRL, and others)
     *
     * @param int|float $value
     * @param string $currency_code
     * @param string $locale
     * 
     * @return string
     */
    public static function currency(int|float $value, string $currency_code, string $locale = 'en_US'): string
    {
        return self::formattedNumber(
            value: $value,
            currencyCode: strtoupper($currency_code),
            locale: $locale,
            style: NumberFormatter::CURRENCY
        );
    }

    /**
     * Converts a numerical value into its percentage representation
     *
     * @param int|float $value
     * @param int $precision
     * 
     * @return string
     * 
     */
    public static function percent(int|float $value, int $precision = 1): string
    {
        return self::formattedNumber(value: $value, precision: $precision, style: NumberFormatter::PERCENT);
    }

    /**
     * Transforms numeric values into their text equivalent
     *
     * @param int|float $value
     * @param string $locale
     * 
     * @return string
     */
    public static function spell(int|float $value, string $locale = 'en_US'): string
    {
        return self::formattedNumber(value: $value, locale: $locale, style: NumberFormatter::SPELLOUT);
    }

    /**
     * Clamp a given number between two other numbers
     *
     * @param  int|float $number
     * @param  int|float $min
     * @param  int|float $max
     * @return int|float
     */
    public static function clamp(int|float $number, int|float $min, int|float $max): int|float
    {
        if ($number > $max) {
            return $max;
        } elseif ($number < $min) {
            return $min;
        }

        return $number;
    }

    /**
     * Formats numbers with thousand separators, decimal precision, and currency symbols
     *
     * @param int|float $value
     * @param string $locale
     * 
     * @return string
     */
    public static function format(
        int|float $value,
        int $precision = 2,
        bool $grouping_used = true,
        string $locale = 'en_US'
    ): string {
        return self::formattedNumber(
            value: $value,
            precision: $precision,
            groupingUsed: $grouping_used,
            locale: $locale
        );
    }

    /**
     * Transforms an integer into its Roman numeral equivalent. 
     * This is useful for formatting dates, numbering lists, or historical references
     *
     * @param int $value
     * 
     * @return string
     */
    public static function toRoman(int $value): string
    {
        $nf = new \MessageFormatter('@numbers=roman', '{0, number}');
        return $nf->format([$value]);
    }

    /**
     * Reduce a large number 
     */
    private static function numberFormat(
        float $number,
        int $decimals = 0,
        string $dec_point = ".",
        string $thousands_sep = ""
    ): float {
        return (float) number_format(
            $number,
            $decimals,
            $dec_point,
            $thousands_sep
        );
    }

    /**
     * Handle NumberFormatter class
     */
    private static function formattedNumber(
        int|float $value,
        string $locale = 'en_US',
        int $style = NumberFormatter::DECIMAL,
        int|float $precision = 2,
        bool $groupingUsed = true,
        string $currencyCode = 'USD',
    ): string {
        $formatter = new NumberFormatter($locale, $style);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
        $formatter->setAttribute(NumberFormatter::GROUPING_USED, $groupingUsed);

        if ($style == NumberFormatter::CURRENCY)
            $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currencyCode);

        return $formatter->format($value);
    }
}
