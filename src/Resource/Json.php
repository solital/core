<?php

namespace Solital\Core\Resource;

class Json
{
    /**
     * @var mixed
     */
    private $constants;

    /**
     * @var string
     */
    private $error = "json_error";

    /**
     * @param  $constants
     */
    public function __construct(int $constants = JSON_UNESCAPED_UNICODE)
    {
        $this->constants = $constants;
    }

    /**
     * @param mixed $array
     * 
     * @return string
     */
    public function encode($array): string
    {
        $json = json_encode($array, $this->constants);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $json;
        } else {
            $error = [$this->error => json_last_error_msg()];
            return json_encode($error);
        }
    }

    /**
     * @param mixed $json
     * @param bool $assoc
     * 
     * @return mixed
     */
    public function decode($json, bool $assoc = false)
    {
        if ($assoc == true) {
            $array = json_decode($json, true);
        } else {
            $array = json_decode($json);
        }

        if (json_last_error() == JSON_ERROR_NONE) {
            return $array;
        } else {
            $error = [$this->error => json_last_error_msg()];
            return json_encode($error);
        }
    }

    /**
     * @param mixed $json
     * @param string $value
     * 
     * @return string|null
     */
    public function inJson($json, string $value): ?string
    {
        $array = $this->decode($json, true);

        if ($array[$value]) {
            return $array[$value];
        } else {
            return null;
        }
    }

    /**
     * @param string $filename
     * 
     * @return array|null
     */
    public function readFile(string $filename, bool $assoc = false): ?array
    {
        $data = file_get_contents($filename);

        $data_decoded = $this->decode($data, $assoc);

        if ($data_decoded) {
            return $data_decoded;
        } else {
            return null;
        }
    }
}
