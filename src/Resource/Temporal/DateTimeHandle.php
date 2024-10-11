<?php

namespace Solital\Core\Resource\Temporal;

use DateTimeZone;
use DateTimeImmutable;
use Deprecated\Deprecated;
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
    protected static function getDateTimeInstance(
        string $date = 'now',
        ?string $timezone = null
    ): DateTimeImmutable {
        if (!is_null($timezone)) self::setTimezone($timezone);

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
            self::$timezone = new DateTimeZone($timezone);
        } catch (\Exception $e) {
            die($e->getMessage() . " in Temporal class");
        }
    }

    /**
     * Return date to UNIX timestamp format
     *
     * @return string
     */
    public function toUnixTimestamp(): string
    {
        return self::$date_time_immutable->getTimestamp();
    }

    /**
     * Return date to atom format
     *
     * @return string
     */
    public function toAtom(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::ATOM);
    }

    /**
     * Return date to cookie format
     *
     * @return string
     */
    public function toCookie(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::COOKIE);
    }

    /**
     * Return date to ISO 8601 expanded format
     *
     * @return string
     */
    public function toISO8601Expanded(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::ISO8601_EXPANDED);
    }

    /**
     * Return date to RFC1036 format
     *
     * @return string
     */
    public function toRFC1036(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC1036);
    }

    /**
     * Return date to RFC1123 format
     *
     * @return string
     */
    public function toRFC1123(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC1123);
    }

    /**
     * Return date to RFC2822 format
     *
     * @return string
     */
    public function toRFC2822(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC2822);
    }

    /**
     * Return date to RFC3339 format
     *
     * @return string
     */
    public function toRFC3339(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC3339);
    }

    /**
     * Return date to RFC3339 Extended format
     *
     * @return string
     */
    public function toRFC3339Extended(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC3339_EXTENDED);
    }

    /**
     * Return date to RFC7231 format
     *
     * @return string
     */
    public function toRFC7231(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC7231);
    }

    /**
     * Return date to RFC822 format
     *
     * @return string
     */
    public function toRFC822(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC822);
    }

    /**
     * Return date to RFC850 format
     *
     * @return string
     */
    public function toRFC850(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RFC850);
    }

    /**
     * Return date to RSS format
     *
     * @return string
     */
    public function toRSS(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::RSS);
    }

    /**
     * Return date to W3C format
     *
     * @return string
     */
    public function toW3C(): string
    {
        return self::$date_time_immutable->format(DateTimeImmutable::W3C);
    }

    /**
     * Return date to custom format
     *
     * @param string $format
     * 
     * @return string
     */
    public function toFormat(string $format): string
    {
        return self::$date_time_immutable->format($format);
    }

    /**
     * Return date using ICU format
     *
     * @param string $pattern
     * 
     * @return string
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
     */
    public function toi18nFormat(string $pattern): string
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault());
        $formatter->setTimeZone(self::$timezone);
        $formatter->setPattern($pattern);
        return $formatter->format(self::$date_time_immutable);
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

    /**
     * Return textual day of the week
     * 
     * @return string
     */
    public function getTextualDay(): string
    {
        return $this->toi18nFormat("EEEE");
    }

    /**
     * Return textual short day of the week
     * 
     * @return string
     */
    public function getTextualShortDay(): string
    {
        return $this->toi18nFormat("EEE");
    }

    public function getYear(): int
    {
        return (int)self::$date_time_immutable->format('Y');
    }

    public function getMonthInt(): int
    {
        return (int)self::$date_time_immutable->format('m');
    }

    public function getTextualMonth(): string
    {
        return $this->toi18nFormat("LLLL");
    }

    /**
     * @deprecated Use `getTextualMonth` instead
     */
    #[Deprecated("Use `getTextualMonth` instead", "4.6")]
    public function getMonthName(): string
    {
        return $this->getTextualMonth();
    }

    public function getTextualShortMonth(): string
    {
        return $this->toi18nFormat("LLL");
    }

    /**
     * @deprecated Use `getTextualShortMonth` instead
     */
    #[Deprecated("Use `getTextualShortMonth` instead", "4.6")]
    public function getMonthShortName(): string
    {
        return $this->getTextualShortMonth();
    }

    /**
     * Return the last day of the month
     *
     * @return int
     */
    public function getLastDayOfMonth(): int
    {
        $time = self::$date_time_immutable->format("Y-m-d");
        $deltaMonth = 0;

        try {
            $year = date('Y', strtotime($time));
            $month = date('m', strtotime($time));

            $increaYear = floor(($deltaMonth + $month - 1) / 12);

            $year += $increaYear;
            $month = (($deltaMonth + $month) % 12) ?: 12;
            $day = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            return (int)date("d", strtotime($year . '-' . $month . '-' . $day));
            //return $time . ' + ' . $deltaMonth . ' => ' . date($format, strtotime($year . '-' . $month . '-' . $day)) . "\n";
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return easter date from current year
     * 
     * @return string
     */
    public function getEasterDate(string $format = "Y-m-d"): string
    {
        $year = self::$date_time_immutable->format("Y");
        return date($format, easter_date($year));
    }

    /**
     * Return easter date for orthodox church from current year
     * 
     * @return string
     */
    public function getEasterDateOrthodox(string $format = "Y-m-d"): string
    {
        $year = (int)self::$date_time_immutable->format("Y");
        $a = $year % 4;
        $b = $year % 7;
        $c = $year % 19;
        $d = (19 * $c + 15) % 30;
        $e = (2 * $a + 4 * $b - $d + 34) % 7;
        $month = floor(($d + $e + 114) / 31);
        $day = (($d + $e + 114) % 31) + 1;
        $add_days = (int)($year / 100) - (int)($year / 400) - 2;
        $timestamp = mktime(0, 0, 0, $month, $day + $add_days, $year);
        $de = date($format, $timestamp);
        return $de;
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

    public function isLeapYear(): bool
    {
        return (self::$date_time_immutable->format('L') == "1") ? true : false;
    }

    private function weekName(): string
    {
        return self::$date_time_immutable->format('D');
    }
}
