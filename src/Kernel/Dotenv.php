<?php

namespace Solital\Core\Kernel;

use Solital\Core\Kernel\Exceptions\DotenvException;

abstract class Dotenv
{
    /**
     * @var array
     */
    private static array $env = [];

    /**
     * @param string $path_to_env
     * 
     * @return void
     */
    public static function env(string $path_to_env): void
    {
        $path = $path_to_env . DIRECTORY_SEPARATOR . ".env";
        $file = file_get_contents($path);
        $lines = file($path);
        $env_to_array = [];
        $array_multi = [];

        foreach ($lines as $line) {
            if (str_contains($line, "#")) {
                $line = str_replace($line, '', $line);
            }

            if ($line == "") {
                $line = str_replace($line, '', $line);
            }

            $env_to_array[] = explode("=", $line);
        }

        foreach ($env_to_array as $file) {
            if (is_string($file[0])) {
                if (isset($file[1])) {
                    $array_multi[] = $file;
                }
            }
        }

        foreach ($array_multi as $file) {
            $value = str_replace('"', '', $file[1]);
            $value = trim($value);
            self::$env[$file[0]] = $value;
            $_ENV[$file[0]] = $value;
            $_SERVER[$file[0]] = $value;
            putenv($file[0] . "=" . $value);
        }

        self::required([
            'ERRORS_DISPLAY',
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'MAIL_DEBUG', 'MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_SECURITY', 'MAIL_PORT',
            'FIRST_SECRET', 'SECOND_SECRET'
        ]);
    }

    /**
     * Verify if isset variable in .env file
     *
     * @param string $field
     * 
     * @return bool
     */
    public static function isset(string $fields): bool
    {
        $fields = strtoupper($fields);

        if (isset($_ENV[$fields]) && isset($_SERVER[$fields]) && getenv($fields) == true) {
            return true;
        }

        return false;
    }

    /**
     * Required fields on .env
     * 
     * @param array $fields
     * 
     * @return void
     */
    private static function required(array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, self::$env)) {
                throw new DotenvException($field . " not found in .env file");
            }
        }
    }
}