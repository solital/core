<?php

use Respect\Validation\Validator;
use Solital\Core\Resource\Session;
use Solital\Core\Resource\Str\Str;
use Solital\Core\Cache\{Cache, Psr6\CacheItemPool};
use Solital\Core\Resource\Collection\ArrayCollection;

/**
 * Remove all get param
 * 
 * @return void
 */
function remove_param(): void
{
    $http = 'http://';

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $http = 'https://';
    }

    $url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $url = parse_url($url);

    if (isset($url['query'])) {
        if (strpos($_SERVER["HTTP_HOST"], "localhost") !== false) {
            header('Refresh: 0, url =' . $url['scheme'] . "://" . $_SERVER["HTTP_HOST"] . $url['path']);
            die;
        } else {
            if (isset($url['path'])) {
                header('Refresh: 0, url =' . $url['scheme'] . "://" . $url['host'] . $url['path']);
                die;
            } else {
                header('Refresh: 0, url =' . $url['scheme'] . "://" . $url['host']);
                die;
            }
        }
    }
}

/**
 * @param mixed $json
 * 
 * @return bool
 */
function is_json($json): bool
{
    $res = Validator::json()->validate($json);

    if ($res == true) {
        return true;
    } else {
        return false;
    }
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
    $http = 'http://';
    if (isset($_SERVER['HTTPS'])) {
        $http = 'https://';
    }
    $url = $http . $_SERVER['HTTP_HOST'];

    if (isset($uri)) {
        $url = $http . $_SERVER['HTTP_HOST'] . "/" . $uri;
    }

    return $url;
}

/**
 * @param string $index
 * @param mixed $value
 * @param mixed $key
 * @param bool $delete
 * 
 * @return mixed
 */
function session(
    string $key,
    mixed $value = null,
    mixed $defaultValue = null,
    bool $delete = false,
    bool $take = false
): mixed {
    if ($value != null) {
        return Session::set($key, $value);
    } elseif ($delete == true) {
        return Session::delete($key);
    } elseif ($take == true) {
        return Session::take($key, $defaultValue);
    }

    return Session::get($key, $defaultValue);
}

/**
 * @param string $name
 * @param array $array
 * 
 * @return mixed
 */
function multi_array_value(string $name, array $array): mixed
{
    foreach ($array as $keys) {
        foreach ($keys as $key => $_user_record) {
            if ($_user_record == $name) {
                return [$key => $_user_record];
            }
        }
    }

    return null;
}

/**
 * @param string $path
 * 
 * @return string|null
 */
function mb_basename(string $path): ?string
{
    if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
        return $matches[1];
    } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
        return $matches[1];
    }

    return '';
}

/**
 * @param string $glue
 * @param array $array
 * @param string $symbol
 * 
 * @return string
 */
function mapped_implode(string $glue, array $array, string $symbol = '='): string
{
    return implode(
        $glue,
        array_map(
            function ($k, $v) use ($symbol) {
                return $k . $symbol . $v;
            },
            array_keys($array),
            array_values($array)
        )
    );
}

/**
 * @param mixed $value
 * 
 * @return ArrayCollection
 */
function collection(mixed $value = null): ArrayCollection
{
    return new ArrayCollection($value);
}

/**
 * @param string $string
 * 
 * @return Str
 */
function str(string $string): Str
{
    return new Str($string);
}
