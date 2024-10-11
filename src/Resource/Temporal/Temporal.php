<?php

namespace Solital\Core\Resource\Temporal;

use DateTimeImmutable;
use Solital\Core\Resource\Temporal\Trait\IntervalTrait;

class Temporal extends DateTimeHandle
{
    use IntervalTrait;

    /**
     * Get current datetime
     *
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function now(?string $timezone = null): static
    {
        self::$date_time = self::getDateTimeInstance(timezone: $timezone);
        self::$date_time_immutable = self::$date_time;
        return new static;
    }

    /**
     * Create datetime from a string
     *
     * @param string $date
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function createDatetime(string $date, ?string $timezone = null): static
    {
        self::$date_time = self::getDateTimeInstance($date, $timezone);
        self::$date_time_immutable = self::$date_time;
        return new static;
    }

    /**
     * Make it convenient and fast to create `DateTime` or `DateTimeImmutable` instances from a UNIX timestamp
     *
     * @param int|float $timestamp
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function createFromTimeStamp(int|float $timestamp, ?string $timezone = null): static
    {
        if (!is_null($timezone)) self::setTimezone($timezone);

        if (PHP_VERSION_ID >= 80400) {
            self::$date_time_immutable = DateTimeImmutable::createFromTimeStamp($timestamp);
        } else {
            if (is_float($timestamp)) {
                $format = "U.u";
            } elseif (is_int($timestamp)) {
                $format = "U";
            }

            self::$date_time_immutable = DateTimeImmutable::createFromFormat(
                $format,
                (string) $timestamp
            );
        }

        return new static;
    }

    /**
     * Alters the timestamp
     *
     * @param string $modifier
     * 
     * @return static
     */
    public function modify(string $modifier): static
    {
        self::$date_time_immutable = self::$date_time->modify($modifier);
        return new static;
    }

    /**
     * Get today datetime
     *
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function today(?string $timezone = null): static
    {
        self::$date_time = self::getDateTimeInstance('today', $timezone);
        self::$date_time_immutable = self::$date_time;
        return new static;
    }

    /**
     * Get yesterday datetime
     *
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function yesterday(?string $timezone = null): static
    {
        self::$date_time = self::getDateTimeInstance('yesterday', $timezone);
        self::$date_time_immutable = self::$date_time;
        return new static;
    }

    /**
     * Get tomorrow datetime
     *
     * @param string|null $timezone
     * 
     * @return static
     */
    public static function tomorrow(?string $timezone = null): static
    {
        self::$date_time = self::getDateTimeInstance('tomorrow', $timezone);
        self::$date_time_immutable = self::$date_time;
        return new static;
    }

    /**
     * Add days to a datetime
     *
     * @param int $add_days
     * 
     * @return static
     */
    public function addDays(int $add_days): static
    {
        $interval = self::daysInterval($add_days);
        self::$date_time_immutable = self::$date_time->add($interval);

        return new static;
    }

    /**
     * Subtract days to a datetime
     *
     * @param int $add_days
     * 
     * @return static
     */
    public function subDays(int $add_days): static
    {
        $interval = self::daysInterval($add_days);
        self::$date_time_immutable = self::$date_time->sub($interval);

        return new static;
    }

    /**
     * Add months to a datetime
     *
     * @param int $add_months
     * 
     * @return static
     */
    public function addMonths(int $add_months): static
    {
        $interval = self::monthsInterval($add_months);
        self::$date_time_immutable = self::$date_time->add($interval);

        return new static;
    }

    /**
     * Subtract months to a datetime
     *
     * @param int $add_months
     * 
     * @return static
     */
    public function subMonths(int $add_months): static
    {
        $interval = self::monthsInterval($add_months);
        self::$date_time_immutable = self::$date_time->sub($interval);

        return new static;
    }

    /**
     * Add years to a datetime
     *
     * @param int $add_years
     * 
     * @return static
     */
    public function addYears(int $add_years): static
    {
        $interval = self::yearsInterval($add_years);
        self::$date_time_immutable = self::$date_time->add($interval);

        return new static;
    }

    /**
     * Subtract years to a datetime
     *
     * @param int $add_years
     * 
     * @return static
     */
    public function subYears(int $add_years): static
    {
        $interval = self::yearsInterval($add_years);
        self::$date_time_immutable = self::$date_time->sub($interval);

        return new static;
    }

    /**
     * Add hours to a datetime
     *
     * @param int $add_hours
     * 
     * @return static
     */
    public function addHours(int $add_hours): static
    {
        $interval = self::hoursInterval($add_hours);
        self::$date_time_immutable = self::$date_time->add($interval);

        return new static;
    }

    /**
     * Subtract hours to a datetime
     *
     * @param int $add_hours
     * 
     * @return static
     */
    public function subHours(int $add_hours): static
    {
        $interval = self::hoursInterval($add_hours);
        self::$date_time_immutable = self::$date_time->sub($interval);

        return new static;
    }

    /**
     * Add minutes to a datetime
     *
     * @param int $add_minutes
     * 
     * @return static
     */
    public function addMinutes(int $add_minutes): static
    {
        $interval = self::minutesInterval($add_minutes);
        self::$date_time_immutable = self::$date_time->add($interval);

        return new static;
    }

    /**
     * Subtract minutes to a datetime
     *
     * @param int $add_minutes
     * 
     * @return static
     */
    public function subMinutes(int $add_minutes): static
    {
        $interval = self::minutesInterval($add_minutes);
        self::$date_time_immutable = self::$date_time->sub($interval);

        return new static;
    }

    /**
     * Set the microsecond part of the timestamp
     *
     * @param int $microseconds
     * 
     * @return static
     */
    public function setMicrosecond(int $microseconds): static
    {
        if (PHP_VERSION_ID >= 80400) {
            self::$date_time_immutable = self::$date_time->setMicrosecond($microseconds);
        } else {
            if ($microseconds < 0 || $microseconds > 999999) {
                throw new \DateRangeError(
                    "DateTimeImmutable::setMicrosecond(): Argument #1 (\$microsecond) must be between 0 and 999999, " . $microseconds . " given"
                );
            }

            self::$date_time_immutable = self::$date_time->setTime(
                self::$date_time->format('H'),
                self::$date_time->format('i'),
                self::$date_time->format('s'),
                $microseconds
            );
        }

        return new static;
    }

    /**
     * Returns the microsecond part of the timestamp as an integer
     *
     * @return int
     */
    public function getMicrosecond(): int
    {
        if (PHP_VERSION_ID >= 80400) return self::$date_time_immutable->getMicrosecond();
        return (int)self::$date_time_immutable->format("u");
    }

    /**
     * Get DateTimeImmutable object
     *
     * @return DateTimeImmutable
     */
    public function getDateTimeImmutableInstance(): DateTimeImmutable
    {
        return self::$date_time;
    }
}
