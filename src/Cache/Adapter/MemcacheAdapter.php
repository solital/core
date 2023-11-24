<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Cache\Exception\CacheAdapterException;

class MemcacheAdapter implements CacheAdapterInterface
{
    /**
     * @var \Memcache
     */
    private \Memcache $memcache;

    /**
     * @param string $host
     * @param string $port
     */
    public function __construct(string $host, string $port)
    {
        if (!class_exists('memcache')) {
            throw new CacheAdapterException("'Memcache' driver not found");
        }

        $this->memcache = new \Memcache;
        $this->memcache->connect($host, $port);
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->memcache->get($key);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        $value = $this->memcache->get($key);

        if (!isset($value) || $value == false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed
    {
        $value = $this->memcache->get($key);

        if (!isset($value) || $value == false) {
            return false;
        }

        return $this->memcache->delete($key);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param int $time
     * 
     * @return mixed
     */
    public function save(string $key, mixed $data, int $time): mixed
    {
        return $this->memcache->set($key, $data, null, $time);
    }
}
