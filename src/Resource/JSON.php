<?php

namespace Solital\Core\Resource;

class JSON
{
    /**
     * @var int
     */
    private int $constants = JSON_UNESCAPED_UNICODE;

    /**
     * Exception thrown if `JSON_THROW_ON_ERROR` option is set for `encode()` or `decode()`
     *
     * @return JSON
     */
    public function enableJsonException(): JSON
    {
        $this->constants = $this->constants | JSON_THROW_ON_ERROR;
        return $this;
    }

    /**
     * Options for JSON encode and decode
     *
     * @param int $constants
     * 
     * @return JSON
     * @see https://www.php.net/manual/en/json.constants.php
     */
    public function setConstants(int $constants): JSON
    {
        $this->constants = $constants;
        return $this;
    }

    /**
     * Returns the JSON representation of a value
     *
     * @param mixed $value
     * @param int $depth
     * 
     * @return string 
     */
    public function encode(mixed $value, int $depth = 512): string
    {
        $json = json_encode($value, $this->constants, $depth);
        $last_error = json_last_error();
        if (json_validate($json, $depth) == false) return $this->error($last_error, false);
        return $json;
    }

    /**
     * Decodes a JSON string
     *
     * @param string $value
     * @param bool $associative
     * @param int $depth
     * 
     * @return mixed 
     */
    public function decode(string $value, bool $associative = false, int $depth = 512): mixed
    {
        $decode = json_decode($value, $associative, $depth, $this->constants);
        $last_error = json_last_error();
        if (json_validate($value, $depth) == false) return $this->error($last_error, $associative);
        return $decode;
    }

    /**
     * Read a value from the JSON file
     * 
     * @param string $json
     * @param string $value
     *
     * @return string|null
     */
    public function inJson(string $json, string $value): ?string
    {
        $array = $this->decode($json, true);
        return ($array[$value]) ? $array[$value] : null;
    }

    /**
     * Read an external file
     *
     * @param string $filename
     * @param bool $associative
     * @param int $depth
     * 
     * @return mixed
     */
    public function readFile(string $filename, bool $associative = false, int $depth = 512): mixed
    {
        $data = file_get_contents($filename);
        $data_decoded = $this->decode($data, $associative, $depth);
        return ($data_decoded) ? $data_decoded : null;
    }

    /**
     * Get json last error
     * 
     * @param int $last_error
     * 
     * @return string|array
     */
    private function error(int $last_error, bool $associative): string|array
    {
        $error = match ($last_error) {
            JSON_ERROR_DEPTH => "The maximum stack depth has been exceeded",
            JSON_ERROR_STATE_MISMATCH => "Invalid or malformed JSON",
            JSON_ERROR_CTRL_CHAR => "Control character error, possibly incorrectly encoded",
            JSON_ERROR_SYNTAX => "Syntax error",
            JSON_ERROR_UTF8 => "Malformed UTF-8 characters, possibly incorrectly encoded",
            JSON_ERROR_RECURSION => "One or more recursive references in the value to be encoded",
            JSON_ERROR_INF_OR_NAN => "One or more NAN or INF values in the value to be encoded",
            JSON_ERROR_UNSUPPORTED_TYPE => "A value of a type that cannot be encoded was given",
            JSON_ERROR_INVALID_PROPERTY_NAME => "A property name that cannot be encoded was given",
            JSON_ERROR_UTF16 => "Malformed UTF-16 characters, possibly incorrectly encoded",
        };

        return ($associative == true) ? ["json_error" => $error] : json_encode(["json_error" => $error]);
    }
}
