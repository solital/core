<?php

namespace Solital\Core\Resource\Temporal\Trait;

use Solital\Core\Resource\Str\Str;

trait TemporalHelpersTrait
{
    public static function formatDate(string $date): string
    {
        if (Str::contains($date, "/")) {
            $date = str_replace("/", "-", $date);
        }

        return $date;
    }
}
