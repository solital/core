<?php

use Solital\Core\Course\Course as Course;
use Solital\Core\Exceptions\RuntimeException;
use Solital\Core\Http\Uri;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\Guardian;

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
 * Get atual url
 * 
 * @param string $uri
 * 
 * @return string
 */
function get_url(string $uri = null): string
{
    return Guardian::getUrl($uri);
}

/**
 * Get a middleware
 * 
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
