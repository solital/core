<?php

namespace Solital\Core\Cache\Psr6;

use Psr\Cache\{CacheItemInterface, CacheItemPoolInterface};

class CacheItemPool implements CacheItemPoolInterface
{
    /**
     * Quaue saved will be elaborate by commit method.
     * 
     * @var array
     */
    protected array $queue_saved = [];

    /**
     * Save the cacheDir by CacheDir::getCacheDir().
     * 
     * @var string
     */
    private string $cache_dir;

    /**
     * @var mixed
     */
    private $cache_item;

    /**
     * CacheItemPool constructor!
     */
    public function __construct()
    {
        $this->cache_dir = CacheDir::getCacheDir();
    }

    /**
     * Returns a Cache Item representing the specified key.
     * 
     * @param string $key
     * 
     * @return CacheItemInterface
     * @throws InvalidArgumentException
     */
    public function getItem(string $key): CacheItemInterface
    {
        $this->cache_item = new CacheItem();
        $this->cache_item->setKey($key);

        if ($this->hasItem($key)) {
            $this->initializeItem($this->cache_item);
        }

        return $this->cache_item;
    }

    /**
     * @param array $keys
     * 
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function getItems(array $keys = []): iterable
    {
        if (empty($keys)) {
            return false;
        }

        $cacheItems = array();

        foreach ($keys as $key) {
            $cacheItems[] = $this->getItem($key);
        }

        return $cacheItems;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * @param string $key
     * 
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasItem(string $key): bool
    {
        return file_exists($this->cache_dir . $key);
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     */
    public function clear(): bool
    {
        if (empty($files = glob($this->cache_dir . '*'))) {
            return false;
        }

        foreach ($files as $file) {
            if (!unlink($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     * 
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteItem(string $key): bool
    {
        return $this->hasItem($key) && unlink($this->cache_dir . $key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     * 
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     * 
     * @return bool
     */
    public function save(CacheItemInterface $cacheItem): bool
    {
        if (!$this->isItemValidForSave($cacheItem)) {
            return false;
        }

        $toWrite = '<?php $item=array(\'value\' => ' . var_export($cacheItem->get(), true) . ', \'expire\' => ' . var_export($cacheItem->getExpires(), true) . '); ?>';

        return ($fileCache = fopen($this->cache_dir . $cacheItem->getKey(), 'w')) &&
                fwrite($fileCache, $toWrite) &&
                fclose($fileCache);
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     * 
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $cacheItem): bool
    {
        return $this->queue_saved[] = $cacheItem;
    }

    /**
     * @return mixed
     */
    public function getQueueSaved()
    {
        return $this->queue_saved;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     */
    public function commit(): bool
    {
        if (empty($this->queue_saved)) {
            return false;
        }

        foreach ($this->queue_saved as $cacheItem) {
            if (!$this->save($cacheItem)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param CacheItemInterface $cacheItem
     * 
     * @return mixed
     */
    protected function isItemValidForSave(CacheItemInterface $cacheItem)
    {
        return !$cacheItem->isKeyEmpty() && !$cacheItem->isValueEmpty();
    }

    /**
     * Inizialize item if it's in cache.
     * Return true if inizialize in OK, or false if item cache is expire.
     * 
     * @param CacheItem $cacheItem
     * 
     * @return bool
     */
    protected function initializeItem(CacheItem $cacheItem)
    {
        $key = $cacheItem->getKey();
        include $this->cache_dir . $key;
        $itemExpire = $item['expire'];
        $expire = $itemExpire === null ? $itemExpire : date_create()->setTimestamp($itemExpire);

        //Check if cache item is valid or not.
        if ($expire !== null && !CacheItem::isDateInFuture($expire)) {
            $this->deleteItem($key);
            return false;
        }

        $cacheItem->set($item['value']);
        if ($expire !== null) {
            $cacheItem->expiresAt($expire);
        }

        return true;
    }
}
