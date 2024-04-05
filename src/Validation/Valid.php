<?php

namespace Solital\Core\Validation;

use Respect\Validation\Validator;

/**
 * @deprecated This class will be removed
 */
class Valid
{
    /**
     * @param string $email
     * 
     * @return string|null
     */
    public static function email(string $email): ?string
    {
        $res = Validator::email()->validate($email);

        if ($res == true) {
            return $email;
        }

        return null;
    }

    /**
     * @param mixed $number
     * 
     * @return mixed|null
     */
    public static function number(mixed $number): mixed
    {
        $res = Validator::number()->validate($number);

        if ($res == true) {
            return $number;
        }

        return null;
    }

    /**
     * @param mixed $value
     * 
     * @return bool
     */
    public static function isNull(mixed $value): bool
    {
        $res = Validator::nullType()->validate($value);

        if ($res == true) {
            return true;
        }

        return false;
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
        }

        return strtolower($value);
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
        }

        return strtoupper($value);
    }

    /**
     * @param string $value
     * 
     * @return bool
     */
    public static function isBase64(string $value): bool
    {
        $res = Validator::base64()->validate($value);

        if ($res == true) {
            return true;
        }

        return false;
    }

    /**
     * @param string $value
     * @param string $identical_to
     * 
     * @return bool
     */
    public static function identical(string $value, string $identical_to): bool
    {
        $res = Validator::identical($value)->validate($identical_to);

        if ($res == true) {
            return true;
        }

        return false;
    }
}
