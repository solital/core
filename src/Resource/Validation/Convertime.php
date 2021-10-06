<?php

namespace Solital\Core\Resource\Validation;

use DateTime;

class Convertime
{
    /**
     * @var DateTime
     */
    private $datetime;

    public function __construct($timezone = "America/Sao_Paulo")
    {
        date_default_timezone_set($timezone);
        $this->datetime = new DateTime();
    }
    /**
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
     * @param string $date
     * @param string $format
     * @param int $add_days
     * 
     * @return string
     */
    public function addDays(string $date, string $format, int $add_days): string
    {
        $date = $this->formatDate($date, "Y-m-d");

        $datetime = DateTime::createFromFormat("Y-m-d", $date);
        $datetime->modify("+$add_days days");

        return $datetime->format($format);
    }

    /**
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

        $this->datetime->setDate($year, $month, $day);

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
     * @param string $date
     * 
     * @return bool
     */
    public function isWeekend(string $date): bool
    {
        $date = $this->formatDate($date, 'Y-m-d');

        if (date('N', strtotime($date)) > 5) {
            return true;
        } else {
            return false;
        }
    }
}
