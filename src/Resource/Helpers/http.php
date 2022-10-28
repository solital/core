<?php

use Solital\Core\Resource\Session;
use Solital\Core\Course\Course as Course;
use Solital\Core\Http\{Uri, Request, Response};
use Solital\Core\Kernel\Application;

/**
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
 * @return Response
 */
function response(): Response
{
    return Course::response();
}

/**
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
 * 
 * @return mixed
 */
function input(string $index = null, string $defaultValue = null, ...$methods)
{
    if ($index !== null) {
        return request()->getInputHandler()->value($index, $defaultValue, ...$methods);
    }

    return request()->getInputHandler();
}

/**
 * @param string $url
 * @param int|null $code
 * 
 * @return void
 */
function redirect(string $url, ?int $code = null): void
{
    if ($code !== null) {
        response()->httpCode($code);
    }

    response()->redirect($url);
    exit;
}

/**
 * @param string $key
 * @param int $limit = 5
 * @param int $seconds = 60
 * 
 * @return bool
 */
function request_limit(string $key, int $limit = 5, int $seconds = 60): bool
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
 * request_repeat
 *
 * @param string $key
 * @param string $value
 * 
 * @return bool
 */
function request_repeat(string $key, string $value): bool
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
    $config = Application::getYamlVariables(5, 'middleware.yaml');
    return $config['middleware'][$value];
}
