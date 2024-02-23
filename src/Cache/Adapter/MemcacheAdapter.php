<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Cache\Exception\CacheAdapterException;
use Solital\Core\Kernel\Application;

class MemcacheAdapter implements CacheAdapterInterface
{
    /**
     * @var \Memcache
     */
    private \Memcache $memcache;

    /**
     * @var int
     */
    private int $ttl = 600;

    /**
     * @param string $host
     * @param string $port
     */
    public function __construct(string $host, int $port)
    {
        if (!class_exists('memcache')) {
            throw new CacheAdapterException("'Memcache' driver not found");
        }

        $this->memcache = new \Memcache;
        $this->memcache->connect($host, (int)$port);

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
        return $this->memcache->get($key);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    #[\Override]
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
    #[\Override]
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
     * 
     * @return mixed
     */
    #[\Override]
    public function save(string $key, mixed $data): mixed
    {
        return $this->memcache->set($key, $data, null, $this->ttl);
    }
}
