<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Cache\Exception\CacheAdapterException;

class APCuAdapter implements CacheAdapterInterface
{
    /**
     * @var int
     */
    private int $ttl = 600;

    /**
     * @param string $ttl
     */
    public function __construct(string $ttl)
    {
        if (!apcu_enabled()) {
            throw new CacheAdapterException('Not connected to apcu cache');
        }

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
        return apcu_fetch($key);
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
        return apcu_delete($key);
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
        return apcu_store($key, $data, $this->ttl);
    }
}
