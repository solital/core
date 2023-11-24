<?php

namespace Solital\Core\Cache\Adapter;

interface CacheAdapterInterface
{
    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function delete(string $key): mixed;

    /**
     * @param string $key
     * @param mixed $data
     * @param int $time
     * 
     * @return bool
     */
    public function save(string $key, mixed $data, int $time): mixed;
}
