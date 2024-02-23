<?php

namespace Solital\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use Solital\Core\Cache\Psr6\CachePool;

class SimpleCache implements CacheInterface
{
    /**
     * @var CachePool
     */
    private CachePool $pool;

    /**
     * @param string|null $drive 
     */
    public function __construct(?string $drive = null)
    {
        $this->pool = new CachePool($drive);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * 
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->pool->getItem($key)->get() ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval|null $ttl
     * 
     * @return bool
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $item = $this->pool->getItem($key)->set($value)->expiresAfter($ttl);
        return $this->pool->save($item);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function delete(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * 
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = (array)$keys;
        
        return array_map(function ($key) use ($default) {
            return $this->get($key, $default);
        }, $keys);
    }

    /**
     * @param iterable $values
     * @param null|int|\DateInterval|null $ttl
     * 
     * @return bool
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $result = true;
        foreach ($values as $key => $value) {
            if ($this->set($key, $values, $ttl) === false) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param iterable $keys
     * 
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if ($this->delete($key) === false) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    /**
     * [Description for clear]
     *
     * @return bool
     * 
     */
    public function clear(): bool
    {
        return $this->pool->clear();
    }
}
