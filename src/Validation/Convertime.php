<?php

namespace Solital\Core\Validation;

class Convertime
{
    /**
     * @var \DateTime
     */
    private readonly \DateTime $datetime;

    /**
     * @param string $datetime
     * @param \DateTimeZone|null|null $timezone
     */
    public function __construct(string $datetime = 'now', \DateTimeZone|null $timezone = null)
    {
        $this->datetime = new \DateTime($datetime, $timezone);
    }

    /**
     * To format a date
     * 
     * @param string $date
     * @param string $format
     * 
     * @return string
     */
    public function formatDate(string $date, string $format): string
    {
        if (strpos($date, "/")) {
            $date = str_replace("/", "-", $date);
        }

        return date($format, strtotime($date));
    }

    /**
     * Add days to a date
     * 
     * @param string $date
     * @param string $format
     * @param int $add_days
     * 
     * @return string
     */
    public function addDays(string $date, string $format, int $add_days): string
    {
        $date = $this->formatDate($date, "Y-m-d");

        $datetime = \DateTime::createFromFormat("Y-m-d", $date);
        $datetime->modify("+$add_days days");

        return $datetime->format($format);
    }

    /**
     * Add months to a date
     * 
     * @param string $date
     * @param string $format
     * @param int $add_months
     * 
     * @return string
     */
    public function addMonth(string $date, string $format, int $add_months): string
    {
        if (strpos($date, "/")) {
            $date = str_replace("/", "-", $date);
        }

        $date = explode('-', $date);

        $day = $date[2];
        $month = $date[1];
        $year = $date[0];

        $this->datetime->setDate((int)$year, (int)$month, (int)$day);

        $future = clone $this->datetime;
        $modifier = "{$add_months} months";
        $future->modify($modifier);

        $pass = clone $future;
        $pass->modify("-{$modifier}");

        while ($this->datetime->format('m') != $pass->format('m')) {
            $future->modify("-1 day");
            $pass->modify("-1 day");
        }

        return $future->format($format);
    }

    /**
     * Add time to another time
     * 
     * @param string $first_hour
     * @param string $second_hour
     * 
     * @return string
     */
    public function addHour(string $first_hour, string $second_hour): string
    {
        $minuts = date("i", strtotime($second_hour));
        $seconds = date("s", strtotime($second_hour));
        $hour = date("H", strtotime($second_hour));

        $temp = strtotime("+$minuts minutes", strtotime($first_hour));
        $temp = strtotime("+$seconds seconds", $temp);
        $temp = strtotime("+$hour hours", $temp);

        return date('H:i:s', $temp);
    }

    /**
     * Check if the date is weekend
     * 
     * @param string $date
     * 
     * @return bool
     */
    public function isWeekend(string $date): bool
    {
        $date = $this->formatDate($date, 'Y-m-d');

        if (date('N', strtotime($date)) > 5) {
            return true;
        }

        return false;
    }
}
