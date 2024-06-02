<?php

use Solital\Core\Course\Course;
use Solital\Core\Kernel\Application;
use Solital\Core\Resource\{Session, Str\Str};
use Solital\Core\Http\{Request, Response};

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
 * Handles the `Response` class
 * 
 * @return Response
 */
function response(): Response
{
    return Course::response();
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
 * Remove all get param
 * 
 * @return void
 */
function remove_param(): void
{
    $config = Application::yamlParse('exceptions.yaml');
    $http = 'http://';

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $http = 'https://';
    }

    $url = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $url = parse_url($url);

    if (isset($url['query'])) {
        if (Str::contains($_SERVER["HTTP_HOST"], "localhost") || $config['production_mode'] == false) {
            $path = $url['scheme'] . "://" . $_SERVER["HTTP_HOST"] . $url['path'];
        } else {
            if (isset($url['path'])) {
                $path = $url['scheme'] . "://" . $url['host'] . $url['path'];
            }

            $path = $url['scheme'] . "://" . $url['host'];
        }

        header('Refresh: 0, url =' . $path);
        die;
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * Returns the IP address of the client.
     *
     * @param null|bool $header_containing_ip_address Default false
     *
     * @return string
     */
    function get_client_ip(?bool $header_containing_ip_address = null): string
    {
        if (!empty($header_containing_ip_address)) {
            return isset($_SERVER[$header_containing_ip_address]) ? trim($_SERVER[$header_containing_ip_address]) : false;
        }

        $knowIPkeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($knowIPkeys as $key) {
            if (array_key_exists($key, $_SERVER) !== true) {
                continue;
            }
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }

        return false;
    }
}

if (!function_exists('is_https')) {
    /**
     * Check to see if the current page is being served over SSL.
     *
     * @return bool
     */
    function is_https(): bool
    {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}

if (!function_exists('is_ajax')) {
    /**
     * Determine if current page request type is ajax.
     *
     * @return bool
     */
    function is_ajax(): bool
    {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            return true;
        }

        return false;
    }
}