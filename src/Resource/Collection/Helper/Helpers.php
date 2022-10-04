<?php

namespace Solital\Core\Resource\Collection\Helper;

use Solital\Core\Resource\Collection\ArrayCollection;

class Helpers
{
    public static function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof ArrayCollection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return self::value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = self::dataGet($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    public static function value($value)
    {
        return $value instanceof \Closure ? self::$value() : $value;
    }
}
