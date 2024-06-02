<?php

namespace Solital\Core\Kernel;

use Solital\Core\Kernel\Exceptions\DotenvException;
use Solital\Core\Resource\Str\Str;

abstract class Dotenv
{
    /**
     * @var array
     */
    private static array $env = [];

    /**
     * @var string
     */
    private static string $env_path = '';

    /**
     * Load .env file
     * 
     * @param string $path_to_env
     * 
     * @return void
     */
    public static function env(string $path_to_env): void
    {
        try {
            self::$env_path = $path_to_env . DIRECTORY_SEPARATOR . ".env";

            if (!Application::fileExistsWithoutCache(self::$env_path)) {
                throw new DotenvException("'.env' file not found");
            }

            $file = file_get_contents(self::$env_path);
            $lines = file(self::$env_path);
            $env_to_array = [];
            $array_multi = [];

            foreach ($lines as $line) {
                if (Str::contains($line, "#")) {
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
                'MAIL_DEBUG', 'MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_SECURITY', 'MAIL_PORT'
            ]);
        } catch (DotenvException $e) {
            die($e->getMessageException());
        }
    }

    /**
     * Verify if isset variable in .env file
     *
     * @param string $field
     * 
     * @return bool
     */
    public static function isset(string $field): bool
    {
        $field = strtoupper($field);

        if (isset($_ENV[$field]) && isset($_SERVER[$field]) && isset(getenv()[$field])) {
            return true;
        }

        return false;
    }

    /**
     * Add a variable at .env file
     * 
     * @param string $key
     * @param string $value
     * @param string $comment
     * 
     * @return bool
     * @throws DotenvException
     */
    public static function add(string $key, string $value, string $comment = ''): bool
    {
        try {
            if (self::$env_path == '') {
                throw new DotenvException("'.env' file not found");
            }

            $key = strtoupper($key);
            $file = fopen(self::$env_path, "a+");

            if (!$file) {
                throw new DotenvException("Failed to open '.env' file");
            }

            if ($comment != '') {
                $comment = "\n\n# " . $comment;
            }

            if (!self::isset($key)) {
                fwrite($file, $comment . "\n" . $key . '=' . $value);

                while (($line = fgets($file)) !== false) {
                    echo $line;
                }

                if (!feof($file)) {
                    throw new DotenvException("fgets() unexpected failure");
                }

                fclose($file);
                return true;
            }

            return false;
        } catch (DotenvException $e) {
            die($e->getMessageException());
        }
    }

    /**
     * Edit a variable at .env file
     *
     * @param string $key
     * @param mixed $value
     * 
     * @return bool|null
     */
    public static function edit(string $key, mixed $value): ?bool
    {
        if (self::$env_path == '') {
            throw new DotenvException("'.env' file not found");
        }

        $file = fopen(self::$env_path, "r");

        if (!$file) {
            throw new DotenvException("Failed to open '.env' file");
        }

        if ($file) {
            while (!feof($file)) {
                $lines[] = fgets($file, 4096);
            }

            fclose($file);
        }

        foreach ($lines as $key_line => $line) {
            if (str_contains($line, $key)) {
                $line = explode("=", $line);
                $original_key = $key_line;
                $line[1] = $value;
                $final = implode("=", $line);
            }
        }

        if (!isset($original_key)) {
            throw new DotenvException("Key '" . $key . "' not exists in '.env' file");
        }

        $lines[$original_key] = $final . "\n";
        $res = file_put_contents(self::$env_path, $lines);

        if ($res != false) {
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
