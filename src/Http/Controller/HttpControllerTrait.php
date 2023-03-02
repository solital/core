<?php

namespace Solital\Core\Http\Controller;

use Solital\Core\Course\Course;
use Solital\Core\Resource\Session;

trait HttpControllerTrait
{
    /**
     * @var mixed
     */
    protected mixed $request;

    /**
     * @param string|null $index
     * @param string|null $defaultValue
     * @param mixed ...$methods
     * 
     * @return mixed
     */
    public function getRequestParams(string $index = null, string $defaultValue = null, ...$methods): mixed
    {
        if ($index !== null) {
            return Course::request()->getInputHandler()->value($index, $defaultValue, ...$methods);
        }

        return Course::request()->getInputHandler();
    }

    /**
     * @param string $url
     * @param int|null $code
     * 
     * @return void
     */
    public function redirect(string $url, ?int $code = null): void
    {
        if ($code !== null) {
            Course::response()->httpCode($code);
        }

        Course::response()->redirect($url);
        exit;
    }

    /**
     * @param string $key
     * @param int $limit
     * @param int $seconds
     * 
     * @return bool
     */
    public function requestLimit(string $key, int $limit = 5, int $seconds = 60): bool
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
     * @param string $key
     * @param string $value
     * 
     * @return bool
     */
    public function requestRepeat(string $key, string $value): bool
    {
        if (Session::has($key) && Session::get($key) == $value) {
            return true;
        }

        Session::set($key, $value);
        return false;
    }
}
