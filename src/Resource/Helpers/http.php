<?php

use Solital\Core\Course\Course as Course;
use Solital\Core\Exceptions\RuntimeException;
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
 */
function input(string $index = null, string $defaultValue = null, ...$methods)
{
    $class = new \ReflectionMethod(Solital\Core\Http\Controller\Controller::class, 'input');
    $class->invoke(Solital\Core\Http\Controller\Controller::class, $index, $defaultValue, $methods);
}

/**
 * @param string $url
 * @param int|null $code
 * 
 * @return void
 */
function to_route(string $url, ?int $code = null): void
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
 */
function request_limit(string $key, int $limit = 5, int $seconds = 60)
{
    $class = new \ReflectionMethod(Solital\Core\Http\Controller\Controller::class, 'requestLimit');
    $class->invoke(Solital\Core\Http\Controller\Controller::class, $key, $limit, $seconds);
}

/**
 * request_repeat
 *
 * @param string $key
 * @param string $value
 */
function request_repeat(string $key, string $value)
{
    $class = new \ReflectionMethod(Solital\Core\Http\Controller\Controller::class, 'requestRepeat');
    $class->invoke(Solital\Core\Http\Controller\Controller::class, $key, $value);
}

/**
 * @param string $value
 * 
 * @return string
 */
function middleware(string $value): string
{
    $config = Application::getYamlVariables(5, 'middleware.yaml');

    if (array_key_exists($value, $config['middleware']) == true) {
        return $config['middleware'][$value];
    }

    throw new RuntimeException("Middleware key '" . $value . "' not exists in middleware.yaml");
}
