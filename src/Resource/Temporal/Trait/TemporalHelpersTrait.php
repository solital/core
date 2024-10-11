<?php

namespace Solital\Core\Resource\Temporal\Trait;

trait TemporalHelpersTrait
{
    public static function formatDate(string $date): string
    {
        if (str_contains($date, "/"))
            $date = str_replace("/", "-", $date);

        return $date;
    }
}
