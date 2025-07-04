<?php

namespace Solital\Core\Http\Controller;

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
    public function getRequestParams(?string $index = null, ?string $defaultValue = null, ...$methods): mixed
    {
        $reflection = new \ReflectionFunction('input');
        return $reflection->invoke($index, $defaultValue, $methods);
    }

    /**
     * @param string $url
     * @param int|null $code
     * 
     * @return void
     */
    public function redirect(string $url, ?int $code = null): void
    {
        $reflection = new \ReflectionFunction('to_route');
        $reflection->invoke($url, $code);
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
        $reflection = new \ReflectionFunction('request_limit');
        return $reflection->invoke($key, $limit, $seconds);
    }

    /**
     * @param string $key
     * @param string $value
     * 
     * @return bool
     */
    public function requestRepeat(string $key, string $value): bool
    {
        $reflection = new \ReflectionFunction('request_repeat');
        return $reflection->invoke($key, $value);
    }
}
