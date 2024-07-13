<?php

namespace Solital\Core\Resource\Temporal\Trait;

use DateInterval;

trait IntervalTrait
{
    protected static function daysInterval(int $number_days): DateInterval
    {
        return new DateInterval("P".$number_days."D");
    }

    protected static function monthsInterval(int $number_months): DateInterval
    {
        return new DateInterval("P".$number_months."M");
    }

    protected static function yearsInterval(int $number_years): DateInterval
    {
        return new DateInterval("P".$number_years."Y");
    }

    protected static function hoursInterval(int $number_hours): DateInterval
    {
        return new DateInterval("PT".$number_hours."H");
    }

    protected static function minutesInterval(int $number_minutes): DateInterval
    {
        return new DateInterval("PT".$number_minutes."M");
    }
}
