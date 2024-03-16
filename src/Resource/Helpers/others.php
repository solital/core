<?php

use Solital\Core\Cache\SimpleCache;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\Guardian;
use Solital\Core\Validation\Convertime;
use Solital\Core\Resource\{Session, Str\Str, Collection\ArrayCollection};
use Solital\Core\Resource\Memorize\{Memorizator, Storage, Utils};

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
        if (str_contains($_SERVER["HTTP_HOST"], "localhost") || $config['production_mode'] == false) {
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
 * Create or return a session value
 * 
 * @param string $key
 * @param mixed $value
 * @param mixed $key
 * @param bool $delete
 * @param bool $take
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
 * Returns trailing name component of path and get rid of trailing slashes/backslashes
 * 
 * @param string $path
 * 
 * @return string
 */
function mb_basename(string $path): string
{
    if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
        return $matches[1];
    } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
        return $matches[1];
    }

    return '';
}

/**
 * Implode an array as key-value pairs. The third parameter is the symbol to be used between key and value
 * 
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
 * Manipulate the `ArrayCollection` class without having to instantiate it.
 * 
 * @param mixed $value
 * 
 * @return ArrayCollection
 */
function collection(mixed $value = null): ArrayCollection
{
    return new ArrayCollection($value);
}

/**
 * Manipulate the `Str` class without having to instantiate it
 * 
 * @param string $string
 * 
 * @return Str
 */
function str(string $string): Str
{
    return new Str($string);
}

/**
 * Return a `Convertime` instance
 * 
 * @param string|null $timezone
 * 
 * @return Convertime
 */
function convertime(?string $timezone = null): Convertime
{
    return new Convertime($timezone);
}

/**
 * Generate a uniquid ID
 * 
 * @param int|float $lenght
 * 
 * @return string
 */
function uniqid_real(int|float $lenght = 13): string
{
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new \Exception("no cryptographically secure random function available");
    }

    return substr(bin2hex($bytes), 0, $lenght);
}

/**
 * Return a `SimpleCache` instance
 *
 * @param string|null $drive
 * 
 * @return SimpleCache
 */
function cache(?string $drive = null): SimpleCache
{
    return new SimpleCache($drive);
}

/**
 * Memorize provides simple in-var cache for closures.
 * At the first call result will be calculated and stored in cache.
 * If the closure with the same arguments was run before memorize will return result from cache without the closure call.
 *
 * @param Closure $lambda
 * @param null|string $paramsHash if pass null paramsHash will be calculated automatically
 * @return mixed
 */
function memorize(Closure $lambda, $paramsHash = null)
{
    $getStorage = function (Closure $lambda) {
        $reflection = new ReflectionFunction($lambda);
        $that = $reflection->getClosureThis();

        if ($that) {
            if (!isset($that->memorizeStorage)) {
                $that->memorizeStorage = new Storage();
            }

            $class = get_class($that);
            $reflection_class = new \ReflectionClass($class);
            $attr = $reflection_class->getAttributes();

            $values = array_map(fn ($attribute) => $attribute->getName(), $attr);

            if (!in_array("AllowDynamicProperties", $values)) {
                throw new \Exception("You must add 'AllowDynamicProperties' attribute on " . $class);
            }

            return $that->memorizeStorage;
        } else {
            global $_globalMemorizeStorage;

            if (is_null($_globalMemorizeStorage)) {
                $_globalMemorizeStorage = new Storage();
            }

            return $_globalMemorizeStorage;
        }
    };

    if (is_null($paramsHash)) {
        $reflection = new ReflectionFunction($lambda);
        $paramsHash = Utils::hash($reflection->getStaticVariables());
    }

    $contextName = Utils::stringify($lambda);
    return Memorizator::memorize($contextName, $lambda, $paramsHash, $getStorage($lambda));
}

/**
 * Get a PSR-11 container
 *
 * @param string $provider
 * 
 * @return mixed
 */
function container(string $provider): mixed
{
    return Application::provider($provider);
}
