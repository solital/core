<?php

namespace Solital\Core\Cache\Psr6;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Solital\Core\Cache\Exception\InvalidArgumentException;
use Solital\Core\Kernel\Application;

class CachePool implements CacheItemPoolInterface
{
    /**
     * @var FileBackend
     */
    private FileBackend $backend;

    /**
     * @var array|CacheItem[]
     */
    private array $items = [];

    /**
     * @param string $directory
     */
    public function __construct(string $directory = "")
    {
        if ($directory == "" || is_null($directory)) {
            $directory = Application::getRootApp("Storage/cache/");
        }

        $this->backend = new FileBackend($directory);
    }

    public function __destruct()
    {
        $this->commit();
    }

    /**
     * @param string $key
     * 
     * @throws InvalidArgumentException
     */
    private function validateKey(string $key)
    {
        if (strlen($key) > 64) {
            throw new InvalidArgumentException("[$key] is invalid");
        }
        if (preg_match('/\A[._a-zA-Z0-9]+\z/', $key) == 0) {
            throw new InvalidArgumentException("[$key] is invalid");
        }
    }

    /**
     * @param string $key
     * 
     * @return CacheItemInterface
     */
    public function getItem(string $key): CacheItemInterface
    {
        $this->validateKey($key);

        if (isset($this->items[$key]) === false) {
            $data = null;
            if ($this->backend->has($key)) {
                $data = $this->backend->get($key);
            }
            $this->items[$key] = new CacheItem($key, $data);
        }

        return $this->items[$key];
    }

    /**
     * @param array $keys
     * 
     * @return iterable
     */
    public function getItems(array $keys = []): iterable
    {
        return array_map(function ($key) {
            return $this->getItem($key);
        }, $keys);
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $this->items = [];
        return $this->backend->clear();
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);

        if (isset($this->items[$key])) {
            $this->items[$key]->set(null);
        }

        unset($this->items[$key]);
        $this->backend->delete($key);

        return true;
    }

    /**
     * @param array $keys
     * 
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        return array_reduce($keys, function ($r, $key) {
            return $r || $this->deleteItem($key);
        }, false);
    }

    /**
     * @param CacheItemInterface $item
     * 
     * @return bool
     */
    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();

        if (isset($this->items[$key]) === false) {
            return false;
        }

        $item = $this->items[$key];
        $item->dirty(false);

        if ($item->isHit()) {
            return $this->backend->save($key, $item->get(), $item->getExpirationTime());
        } else {
            return $this->backend->delete($key);
        }
    }

    /**
     * @param CacheItemInterface $item
     * 
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        if (isset($this->items[$key])) {
            $this->items[$key]->dirty(true);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isDirty()) {
                $this->save($item);
            }
        }
        return true;
    }
}
