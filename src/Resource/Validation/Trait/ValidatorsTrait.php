<?php

namespace Solital\Core\Resource\Validation\Trait;

use Solital\Core\Resource\Validation\EnvHelpers;

trait ValidatorsTrait
{
    public static $trues = ['1', 1, 'true', true, 'yes', 'on'];
    public static $falses = ['0', 0, 'false', false, 'no', 'off'];
    private static $alpha_regex = 'a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ';

    /**
     * Ensures the specified key value exists and is not empty (not null, not empty string, not empty array).
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_required($field, array $input, array $params = [], $value = null)
    {
        return isset($value) && !self::is_empty($value);
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     *
     * @example_parameter one;two;use array format if one of the values contains semicolons
     *
     * @param string $field
     * @param array  $input
     * @param array $params
     *
     * @return bool
     */
    protected function validate_contains($field, array $input, array $params)
    {
        $value = mb_strtolower(trim($input[$field]));

        $params = array_map(static function ($value) {
            return mb_strtolower(trim($value));
        }, $params);

        return in_array($value, $params, true);
    }

    /**
     * Verify that a value is contained within the pre-defined value set. Error message will NOT show the list of possible values.
     *
     * @example_parameter value1;value2
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_contains_list($field, $input, array $params)
    {
        return $this->validate_contains($field, $input, $params);
    }

    /**
     * Verify that a value is contained within the pre-defined value set. Error message will NOT show the list of possible values.
     *
     * @example_parameter value1;value2
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_doesnt_contain_list($field, $input, array $params)
    {
        return !$this->validate_contains($field, $input, $params);
    }

    /**
     * Determine if the provided value is a valid boolean. Returns true for: yes/no, on/off, 1/0, true/false. In strict mode (optional) only true/false will be valid which you can combine with boolean filter.
     *
     * @example_parameter strict
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_boolean($field, array $input, array $params = [], $value = null)
    {
        if (isset($params[0]) && $params[0] === 'strict') {
            return in_array($input[$field], [true, false], true);
        }

        $booleans = [];
        foreach (self::$trues as $true) {
            $booleans[] = $true;
        }
        foreach (self::$falses as $false) {
            $booleans[] = $false;
        }

        return in_array($input[$field], $booleans, true);
    }

    /**
     * Determine if the provided email has valid format.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value individual value (in case of array)
     *
     * @return bool
     */
    protected function validate_valid_email($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     *
     * @example_parameter 240
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_max_len($field, array $input, array $params = [], $value = null)
    {
        return mb_strlen($value) <= (int)$params[0];
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     *
     * @example_parameter 4
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_min_len($field, array $input, array $params = [], $value = null)
    {
        return mb_strlen($value) >= (int)$params[0];
    }

    /**
     * Determine if the provided value length matches a specific value.
     *
     * @example_parameter 5
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_exact_len($field, array $input, array $params = [], $value = null)
    {
        return mb_strlen($value) == (int)$params[0];
    }

    /**
     * Determine if the provided value length is between min and max values.
     *
     * @example_parameter 3;11
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_between_len($field, $input, array $params, $value = null)
    {
        return $this->validate_min_len($field, $input, [$params[0]], $value)
            && $this->validate_max_len($field, $input, [$params[1]], $value);
    }

    /**
     * Determine if the provided value contains only alpha characters.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     * @return bool
     */
    protected function validate_alpha($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value contains only alpha-numeric characters.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_alpha_numeric($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '0-9])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value contains only alpha characters with dashed and underscores.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_alpha_dash($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '_-])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with dashed and underscores.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_alpha_numeric_dash($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '0-9_-])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_alpha_numeric_space($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '\s0-9])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value contains only alpha characters with spaces.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_alpha_space($field, array $input, array $params = [], $value = null)
    {
        return preg_match('/^([' . self::$alpha_regex . '\s])+$/i', $value) > 0;
    }

    /**
     * Determine if the provided value is a valid number or numeric string.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_numeric($field, array $input, array $params = [], $value = null)
    {
        return is_numeric($value);
    }

    /**
     * Determine if the provided value is a valid integer.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_integer($field, array $input, array $params = [], $value = null)
    {
        return !(filter_var($value, FILTER_VALIDATE_INT) === false || is_bool($value) || is_null($value));
    }

    /**
     * Determine if the provided value is a valid float.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_float($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Determine if the provided value is a valid URL.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_valid_url($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Determine if a URL exists & is accessible.
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     *
     * @return bool
     */
    protected function validate_url_exists($field, array $input, array $params = [], $value = null)
    {
        $url = parse_url(mb_strtolower($value));

        if (isset($url['host'])) {
            $url = $url['host'];
        }

        return EnvHelpers::checkdnsrr(idn_to_ascii($url, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46), 'A') !== false;
    }

    /**
     * Determine if the provided value is a valid IP address.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_ip($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     *
     * @see What about private networks? What about loop-back address? 127.0.0.1 http://en.wikipedia.org/wiki/Private_network
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_ipv4($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_ipv6($field, array $input, array $params = [], $value = null)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Determine if the input is a valid credit card number.
     *
     * @see http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_cc($field, array $input, array $params = [], $value = null)
    {
        $number = preg_replace('/\D/', '', $value);

        $number_length = mb_strlen($number);

        /**
         * Bail out if $number_length is 0.
         * This can be the case if a user has entered only alphabets
         *
         * @since 1.5
         */
        if ($number_length == 0) {
            return false;
        }

        $parity = $number_length % 2;

        $total = 0;

        for ($i = 0; $i < $number_length; ++$i) {
            $digit = $number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $total += $digit;
        }

        return $total % 10 == 0;
    }

    /**
     * Determine if the input is a valid human name.
     *
     * @see https://github.com/Wixel/GUMP/issues/5
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_name($field, array $input, array $params = [], $value = null)
    {
        return preg_match("/^([a-z \p{L} '-])+$/i", $value) > 0;
    }

    /**
     * Determine if the provided input is likely to be a street address using weak detection.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_street_address($field, array $input, array $params = [], $value = null)
    {
        // Theory: 1 number, 1 or more spaces, 1 or more words
        $has_letter = preg_match('/[a-zA-Z]/', $value);
        $has_digit = preg_match('/\d/', $value);
        $has_space = preg_match('/\s/', $value);

        return $has_letter && $has_digit && $has_space;
    }

    /**
     * Determine if the provided value is a valid IBAN.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_iban($field, array $input, array $params = [], $value = null)
    {
        $character = [
            'A' => 10,
            'C' => 12,
            'D' => 13,
            'E' => 14,
            'F' => 15,
            'G' => 16,
            'H' => 17,
            'I' => 18,
            'J' => 19,
            'K' => 20,
            'L' => 21,
            'M' => 22,
            'N' => 23,
            'O' => 24,
            'P' => 25,
            'Q' => 26,
            'R' => 27,
            'S' => 28,
            'T' => 29,
            'U' => 30,
            'V' => 31,
            'W' => 32,
            'X' => 33,
            'Y' => 34,
            'Z' => 35,
            'B' => 11
        ];

        if (!preg_match("/\A[A-Z]{2}\d{2} ?[A-Z\d]{4}( ?\d{4}){1,} ?\d{1,4}\z/", $value)) {
            return false;
        }

        $iban = str_replace(' ', '', $value);
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = strtr($iban, $character);

        return bcmod($iban, 97) == 1;
    }

    /**
     * Determine if the provided input is a valid date (ISO 8601) or specify a custom format (optional).
     *
     * @example_parameter d/m/Y
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_date($field, array $input, array $params = [], $value = null)
    {
        // Default
        if (count($params) === 0) {
            $cdate1 = date('Y-m-d', strtotime($value));
            $cdate2 = date('Y-m-d H:i:s', strtotime($value));

            return !($cdate1 != $value && $cdate2 != $value);
        }

        $date = \DateTime::createFromFormat($params[0], $value);

        return !($date === false || $value != date($params[0], $date->getTimestamp()));
    }

    /**
     * Determine if the provided input meets age requirement (ISO 8601). Input should be a date (Y-m-d).
     *
     * @example_parameter 18
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     * @throws Exception
     */
    protected function validate_min_age($field, array $input, array $params, $value = null)
    {
        $inputDatetime = new \DateTime(EnvHelpers::date('Y-m-d', strtotime($value)));
        $todayDatetime = new \DateTime(EnvHelpers::date('Y-m-d'));

        $interval = $todayDatetime->diff($inputDatetime);
        $yearsPassed = $interval->y;

        return $yearsPassed >= $params[0];
    }

    /**
     * Determine if the provided numeric value is lower or equal to a specific value.
     *
     * @example_parameter 50
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     * @return bool
     */
    protected function validate_max_numeric($field, array $input, array $params = [], $value = null)
    {
        return is_numeric($value) && is_numeric($params[0]) && ($value <= $params[0]);
    }

    /**
     * Determine if the provided numeric value is higher or equal to a specific value.
     *
     * @example_parameter 1
     *
     * @param string $field
     * @param array  $input
     * @param array  $params
     * @return bool
     */
    protected function validate_min_numeric($field, array $input, array $params = [], $value = null)
    {
        return is_numeric($value) && is_numeric($params[0]) && ($value >= $params[0]);
    }

    /**
     * Determine if the provided value starts with param.
     *
     * @example_parameter Z
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     * @return bool
     */
    protected function validate_starts($field, array $input, array $params, $value = null)
    {
        return strpos($value, $params[0]) === 0;
    }

    /**
     * Determine if the file was successfully uploaded.
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_required_file($field, array $input, array $params = [], $value = null)
    {
        return isset($input[$field]) && is_array($input[$field]) && $input[$field]['error'] === 0;
    }

    /**
     * Check the uploaded file for extension. Doesn't check mime-type yet.
     *
     * @example_parameter png;jpg;gif
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_extension($field, $input, array $params, $value = null)
    {
        if (is_array($input[$field]) && $input[$field]['error'] === 0) {
            $params = array_map(function ($v) {
                return trim(mb_strtolower($v));
            }, $params);

            $path_info = pathinfo($input[$field]['name']);
            $extension = $path_info['extension'] ?? null;

            return $extension && in_array(mb_strtolower($extension), $params, true);
        }

        return false;
    }

    /**
     * Determine if the provided field value equals current field value.
     *
     * @example_parameter other_field_name
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_equalsfield($field, array $input, array $params, $value = null)
    {
        return $input[$field] == $input[$params[0]];
    }

    /**
     * Determine if the provided field value is a valid GUID (v4)
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_guidv4($field, array $input, array $params = [], $value = null)
    {
        return preg_match("/\{?[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}\}?$/", $value) > 0;
    }

    /**
     * Determine if the provided value is a valid phone number.
     *
     * @example_value 5555425555
     * @example_value 555-555-5555
     * @example_value 1(519) 555-4444
     * @example_value 1-555-555-5555
     * @example_value 1-(555)-555-5555
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_phone_number($field, array $input, array $params = [], $value = null)
    {
        $regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';

        return preg_match($regex, $value) > 0;
    }

    /**
     * Custom regex validator.
     *
     * @example_parameter /test-[0-9]{3}/
     * @example_value     test-123
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_regex($field, array $input, array $params = [], $value = null)
    {
        return preg_match($params[0], $value) > 0;
    }

    /**
     * Determine if the provided value is a valid JSON string.
     *
     * @example_value {"test": true}
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_json_string($field, array $input, array $params = [], $value = null)
    {
        return is_string($input[$field])
            && is_array(json_decode($value, true))
            && (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Check if an input is an array and if the size is more or equal to a specific value.
     *
     * @example_parameter 1
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_array_size_greater($field, array $input, array $params, $value = null)
    {
        if (!is_array($input[$field]) || count($input[$field]) < $params[0]) {
            return false;
        }

        return true;
    }

    /**
     * Check if an input is an array and if the size is less or equal to a specific value.
     *
     * @example_parameter 1
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_array_size_lesser($field, array $input, array $params = [], $value = null)
    {
        if (!is_array($input[$field]) || count($input[$field]) > $params[0]) {
            return false;
        }

        return true;
    }

    /**
     * Check if an input is an array and if the size is equal to a specific value.
     *
     * @example_parameter 1
     *
     * @param string $field
     * @param array $input
     * @param array $params
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate_valid_array_size_equal($field, array $input, array $params = [], $value = null)
    {
        return !(!is_array($input[$field]) || count($input[$field]) != $params[0]);
    }
}
