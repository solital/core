<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Cache\Exception\CacheAdapterException;
use Solital\Core\Kernel\Application;

class MemcachedAdapter implements CacheAdapterInterface
{
    /**
     * @var \Memcached
     */
    private \Memcached $memcached;

    /**
     * @var int
     */
    private int $ttl = 600;

    /**
     * @param string $host
     * @param string $port
     */
    public function __construct(string $host, string $port)
    {
        if (!class_exists('memcached')) {
            throw new CacheAdapterException("'Memcached' driver not found");
        }

        $this->memcached = new \Memcached;
        $this->memcached->addServer($host, (int)$port);

        if ($this->memcached->getStats() == false) {
            throw new CacheAdapterException("Not connected to memcached server");
        }

        $yaml_file = Application::yamlParse('cache.yaml');
        $this->ttl = $yaml_file['cache_ttl'];
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    #[\Override]
    public function get(string $key): mixed
    {
        return $this->memcached->get($key);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    #[\Override]
    public function has(string $key): bool
    {
        $value = $this->memcached->get($key);

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
    #[\Override]
    public function delete(string $key): mixed
    {
        $value = $this->memcached->get($key);

        if (!isset($value) || $value == false) {
            return false;
        }

        return $this->memcached->delete($key);
    }

    /**
     * @param string $key
     * @param mixed $data
     * 
     * @return mixed
     */
    #[\Override]
    public function save(string $key, mixed $data): mixed
    {
        return $this->memcached->set($key, $data, $this->ttl);
    }
}
