<?php

namespace Solital\Core\Resource\Validation;

class Valid
{
    /**
     * @param string $email
     * 
     * @return string|null
     */
    public static function email(string $email): ?string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        } else {
            return null;
        }
    }

    /**
     * @param mixed $number
     * 
     * @return mixed|null
     */
    public static function number($number)
    {
        if (is_int($number)) {
            if (filter_var($number, FILTER_VALIDATE_INT)) {
                return $number;
            } else {
                return null;
            }
        } elseif (is_float($number)) {
            if (filter_var($number, FILTER_VALIDATE_FLOAT)) {
                return $number;
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     * 
     * @return bool
     */
    public static function isNull($value): bool
    {
        if (is_null($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $value
     * 
     * @return string
     */
    public static function isLower(string $value): string
    {
        if (ctype_lower($value)) {
            return $value;
        } else {
            return strtolower($value);
        }
    }

    /**
     * @param string $value
     * 
     * @return string
     */
    public static function isUpper(string $value): string
    {
        if (ctype_upper($value)) {
            return $value;
        } else {
            return strtoupper($value);
        }
    }
}
