<?php

use Solital\Core\Course\Course as Course;
use Solital\Core\Exceptions\RuntimeException;
use Solital\Core\Http\{Uri, Request, Response};
use Solital\Core\Kernel\Application;
use Solital\Core\Resource\Session;

/**
 * Handles the `URI` class
 * 
 * @param string|null $name
 * @param string|array|null $parameters
 * @param array|null $getParams
 * 
 * @return Uri
 * 
 * @throws \InvalidArgumentException
 */
function url(?string $name = null, $parameters = null, ?array $getParams = null): Uri
{
    return Course::getUri($name, $parameters, $getParams);
}

/**
 * Handles the `Response` class
 * 
 * @return Response
 */
function response(): Response
{
    return Course::response();
}

/**
 * Handles the `Request` class
 * 
 * @return Request
 */
function request(): Request
{
    return Course::request();
}

/**
 * Get input class
 * 
 * @param string|null $index Parameter index name
 * @param string|null $defaultValue Default return value
 * @param array ...$methods Default methods
 */
function input(string $index = null, string $defaultValue = null, ...$methods)
{
    if ($index !== null) {
        return Course::request()->getInputHandler()->value($index, $defaultValue, ...$methods);
    }

    return Course::request()->getInputHandler();
}

/**
 * Redirect to another route
 * 
 * @param string $url
 * @param int|null $code
 * 
 * @return void
 */
function to_route(string $url, ?int $code = null): void
{
    if ($code !== null) {
        Course::response()->httpCode($code);
    }

    Course::response()->redirect($url);
    exit;
}

/**
 * Defines a limit on requests that can be made at a certain time
 * 
 * @param string $key
 * @param int $limit = 5
 * @param int $seconds = 60
 */
function request_limit(string $key, int $limit = 5, int $seconds = 60)
{
    if (Session::has($key) && $_SESSION[$key]['time'] >= time() && $_SESSION[$key]['requests'] < $limit) {
        Session::set($key, [
            'time' => time() + $seconds,
            'requests' => $_SESSION[$key]['requests'] + 1
        ]);

        return false;
    }

    if (Session::has($key) && $_SESSION[$key]['time'] >= time() && $_SESSION[$key]['requests'] >= $limit) {
        return true;
    }

    Session::set($key, [
        'time' => time() + $seconds,
        'requests' => 1
    ]);

    return false;
}

/**
 * Checks if a value was previously sent in the requisition
 *
 * @param string $key
 * @param string $value
 */
function request_repeat(string $key, string $value)
{
    if (Session::has($key) && Session::get($key) == $value) {
        return true;
    }

    Session::set($key, $value);
    return false;
}

/**
 * @param string $value
 * 
 * @return string
 */
function middleware(string $value): string
{
    $config = Application::yamlParse('middleware.yaml');

    if (array_key_exists($value, $config['middleware']) == true) {
        return $config['middleware'][$value];
    }

    throw new RuntimeException("Middleware key '" . $value . "' not exists in middleware.yaml");
}
