<?php

namespace Solital\Core\Resource;

use Solital\Core\Kernel\Application;

class JSON
{
    /**
     * @var array
     */
    private array $throws_error;

    /**
     * @param int $constants
     */
    public function __construct(
        private int $constants = JSON_UNESCAPED_UNICODE,
    ) {
        $this->throws_error = Application::getYamlVariables(5, 'bootstrap.yaml');
        $this->throwsError($constants);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function encode(mixed $value): string
    {
        $json = json_encode($value, $this->constants);

        if (json_validate($json) == false) {
            return $this->error();
        }

        return $json;
    }

    /**
     * @param mixed $value
     * @param bool $associative_array
     *
     * @return mixed
     */
    public function decode(mixed $value, bool $associative_array = false): mixed
    {
        if (json_validate($value) == false) {
            return $this->error();
        }

        $array = json_decode($value, flags: $this->constants);

        if ($associative_array == true) {
            $array = json_decode($value, true, flags: $this->constants);
        }

        return $array;
    }

    /**
     * @return mixed
     */
    private function error(): mixed
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }

        $error = ['json_error' => $error];
        return json_encode($error);
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

    /**
     * @param int $constants
     * 
     * @return void
     */
    private function throwsError(int $constants): void
    {
        if (array_key_exists('json_exception', $this->throws_error)) {
            $this->constants = $constants;

            if ($this->throws_error['json_exception'] == true) {
                $this->constants = $constants | JSON_THROW_ON_ERROR;
            } else {
                $this->constants = $constants;
            }
        } else {
            $this->constants = $constants;
        }
    }
}
