<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Cache\Exception\CacheAdapterException;

class YacAdapter implements CacheAdapterInterface
{
    /**
     * @var int
     */
    private int $ttl = 600;

    /**
     * @var mixed
     */
    private mixed $yac;

    /**
     * @param string $ttl
     */
    public function __construct(string $ttl)
    {
        if (!extension_loaded('yac')) {
            throw new CacheAdapterException('YAC extension not found');
        }

        $this->yac = new \Yac();
        $this->ttl = (int)$ttl;
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    #[\Override]
    public function get(string $key): mixed
    {
        return $this->yac->get($key);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    #[\Override]
    public function has(string $key): bool
    {
        $value = $this->get($key);

        if (!empty($value) || $value != false) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    #[\Override]
    public function delete(string $key): mixed
    {
        return $this->yac->delete($key);
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
        return $this->yac->set($key, $data, $this->ttl);
    }
}
