<?php

namespace Solital\Core\Resource;

use NumberFormatter;

class Number
{
    /**
     * Reduce a large number
     *
     * @param float $number
     * @param int|null $decimals
     * 
     * @return float
     */
    public static function reduce(float $number, ?int $decimals = null): float
    {
        if (!is_null($decimals) && $decimals > 0) {
            return self::numberFormat($number, $decimals);
        }

        return self::numberFormat($number, 2);
    }

    /**
     * Get currency format
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
     * Get percent format
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
     * Spellout rule-based format
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
        return min(max($number, $min), $max);
    }

    /**
     * Format a value
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
     * Reduce a large number 
     */
    private static function numberFormat(
        float $number,
        int $decimals = 0,
        string $dec_point = ".",
        string $thousands_sep = ""
    ): float {
        return (float) number_format($number, $decimals, $dec_point, $thousands_sep);
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

        if ($style == NumberFormatter::CURRENCY) {
            $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currencyCode);
        }

        return $formatter->format($value);
    }
}
