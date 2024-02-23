<?php

namespace Solital\Core\Cache\Psr6;

use Solital\Core\Kernel\Application;
use Solital\Core\Cache\Exception\InvalidArgumentException;
use Psr\Cache\{CacheItemInterface, CacheItemPoolInterface};
use Solital\Core\Cache\Adapter\{APCuAdapter, FileBackendAdapter, MemcacheAdapter, MemcachedAdapter};

class CachePool implements CacheItemPoolInterface
{
    /**
     * @var mixed
     */
    private mixed $backend;

    /**
     * @var array|CacheItem[]
     */
    private array $items = [];

    /**
     * @var string
     */
    private string $cache_drive;

    /**
     * @param string|null $drive
     */
    public function __construct(?string $drive = null)
    {
        $yaml_data = Application::yamlParse('cache.yaml');
        $this->switchDriveAtYamlOrParam($drive, $yaml_data);
    }

    /**
     * Switch cache drive between param and yaml file
     *
     * @param string|null $drive
     * @param array $yaml_data
     * 
     * @return void
     */
    private function switchDriveAtYamlOrParam(?string $drive, array $yaml_data): void
    {
        if (!is_null($drive)) {
            $this->cache_drive = $drive;
        } else {
            if (!array_key_exists('cache_drive', $yaml_data)) {
                throw new InvalidArgumentException("Variable 'cache_drive' not found at 'cache.yaml' file");
            }

            $this->cache_drive = $yaml_data['cache_drive'];
        }

        switch ($this->cache_drive) {
            case 'file':
                $directory = Application::getRootApp("Storage/cache/");
                $this->backend = new FileBackendAdapter($directory, $yaml_data['cache_ttl']);
                break;

            case 'memcache':
                $this->backend = new MemcacheAdapter($yaml_data['cache_host'], $yaml_data['cache_port']);
                break;

            case 'memcached':
                $this->backend = new MemcachedAdapter($yaml_data['cache_host'], $yaml_data['cache_port']);
                break;

            case 'apcu':
                $this->backend = new APCuAdapter($yaml_data['cache_ttl']);
                break;

            default:
                throw new InvalidArgumentException("Cache drive isn't exist or not supported at 'cache.yaml' file");
                break;
        }
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
