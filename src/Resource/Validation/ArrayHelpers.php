<?php

namespace Solital\Core\Resource\Validation;

use ArrayAccess;

class ArrayHelpers
{
    public static function data_get($target, $key, $default = null)
    {
        if (is_null($key)) return $target;
        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if (! is_array($target)) return $default;
                $result = [];

                foreach ($target as $item) {
                    $result[] = self::data_get($item, $key);
                }

                return in_array('*', $key) ? self::collapse($result) : $result;
            }

            if (self::accessible($target) && self::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists(ArrayAccess|array $array, string|int $key): bool
    {
        return ($array instanceof ArrayAccess) ?
            $array->offsetExists($key) :
            array_key_exists($key, $array);
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array  $array
     * @return array
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (! is_array($values)) continue;
            $results[] = $values;
        }

        return array_merge([], ...$results);
    }
}
