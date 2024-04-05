<?php

namespace Solital\Core\Resource\Temporal;

use DateTimeZone;
use DateTimeImmutable;
use Solital\Core\Resource\Temporal\Trait\TemporalHelpersTrait;

abstract class DateTimeHandle
{
    use TemporalHelpersTrait;

    /**
     * @var DateTimeZone|null|null
     */
    protected static ?DateTimeZone $timezone = null;

    /**
     * @var DateTimeImmutable
     */
    protected static DateTimeImmutable $date_time;

    /**
     * @var DateTimeImmutable
     */
    protected static DateTimeImmutable $date_time_immutable;

    /**
     * Get a DateTime instance
     *
     * @param string $date
     * @param string|null $timezone
     * 
     * @return DateTimeImmutable
     */
    protected static function getDateTimeInstance(string $date = 'now', ?string $timezone = null): DateTimeImmutable
    {
        if (!is_null($timezone)) {
            self::setTimezone($timezone);
        }

        $date = self::formatDate($date);

        try {
            return new DateTimeImmutable($date, self::$timezone);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Set a timezone for current time
     *
     * @param string $timezone
     * 
     * @return void
     */
    protected static function setTimezone(string $timezone): void
    {
        try {
            self::$timezone = new \DateTimeZone($timezone);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function toUnixTimestamp(): string
    {
        return self::$date_time_immutable->getTimestamp();
    }

    public function toAtom(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::ATOM);
    }

    public function toCookie(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::COOKIE);
    }

    public function toISO8601Expanded(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::ISO8601_EXPANDED);
    }

    public function toRFC1036(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC1036);
    }

    public function toRFC1123(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC1123);
    }

    public function toRFC2822(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC2822);
    }

    public function toRFC3339(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC3339);
    }

    public function toRFC3339Extended(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC3339_EXTENDED);
    }

    public function toRFC7231(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC7231);
    }

    public function toRFC822(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC822);
    }

    public function toRFC850(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC850);
    }

    public function toRSS(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RSS);
    }

    public function toW3C(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::W3C);
    }

    public function toFormat(string $format): string
    {
        return self::$date_time_immutable->format($format);
    }

    public function getHour(): int
    {
        return (int)self::$date_time_immutable->format('H');
    }

    public function getMinute(): int
    {
        return (int)self::$date_time_immutable->format('i');
    }

    public function getSecond(): int
    {
        return (int)self::$date_time_immutable->format('s');
    }

    public function getDay(): int
    {
        return (int)self::$date_time_immutable->format('d');
    }

    public function getYear(): int
    {
        return (int)self::$date_time_immutable->format('Y');
    }

    public function getMonthInt(): int
    {
        return (int)self::$date_time_immutable->format('m');
    }

    public function getMonthName(): string
    {
        return self::$date_time_immutable->format('F');
    }

    public function getMonthShortName(): string
    {
        return self::$date_time_immutable->format('M');
    }

    public function isMonday(): bool
    {
        return ($this->weekName() === 'Mon') ? true : false;
    }

    public function isTuesday(): bool
    {
        return ($this->weekName() === 'Thu') ? true : false;
    }

    public function isWednesday(): bool
    {
        return ($this->weekName() === 'Wed') ? true : false;
    }

    public function isThursday(): bool
    {
        return ($this->weekName() === 'Thi') ? true : false;
    }

    public function isFriday(): bool
    {
        return ($this->weekName() === 'Fri') ? true : false;
    }

    public function isSaturday(): bool
    {
        return ($this->weekName() === 'Sat') ? true : false;
    }

    public function isSunday(): bool
    {
        return ($this->weekName() === 'Sun') ? true : false;
    }

    public function isWeekend(): bool
    {
        return ($this->weekName() === 'Sat' || $this->weekName() === 'Sun') ? true : false;
    }

    private function weekName(): string
    {
        return self::$date_time_immutable->format('D');
    }
}
