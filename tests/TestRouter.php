<?php

namespace Solital\Test;

use Solital\Core\Course\Course;
use Solital\Core\Http\Uri;

class TestRouter extends Course
{
    public static function reset(): void
    {
        static::$router = null;
    }

    public static function debugNoReset(string $testUrl, string $testMethod = 'get'): void
    {
        $request = static::request();

        $request->setUrl((new Uri($testUrl)));
        $request->setMethod($testMethod);

        static::start();
    }

    public static function debug(string $testUrl, string $testMethod = 'get', bool $reset = true): void
    {
        try {
            static::debugNoReset($testUrl, $testMethod);
        } catch (\Exception $e) {
            static::$defaultNamespace = null;
            static::router()->reset();
            throw $e;
        }

        if ($reset === true) {
            static::$defaultNamespace = null;
            static::router()->reset();
        }
    }

    public static function debugOutput(string $testUrl, string $testMethod = 'get', bool $reset = true): string
    {
        $response = null;

        // Route request
        ob_start();
        static::debug($testUrl, $testMethod, $reset);
        $response = ob_get_clean();

        // Return response
        return $response;
    }

    public static function debugOutputNoReset(string $testUrl, string $testMethod = 'get', bool $reset = true): string
    {
        $response = null;

        // Route request
        ob_start();
        static::debugNoReset($testUrl, $testMethod);
        $response = ob_get_clean();

        // Return response
        return $response;
    }
}
