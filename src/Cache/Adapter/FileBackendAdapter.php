<?php

namespace Solital\Core\Cache\Adapter;

use Solital\Core\Cache\Adapter\CacheAdapterInterface;
use Solital\Core\Kernel\Application;

class FileBackendAdapter implements CacheAdapterInterface
{
    /**
     * @param string $directory
     */
    public function __construct(
        private string $directory,
        private int $ttl = 600
    ) {
        $this->directory = Application::getRootApp("Storage/cache/");
    }

    /**
     * @param string $key
     * 
     * @return string
     */
    private function path(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * @param string $key
     * 
     * @return bool
     */
    #[\Override]
    public function has(string $key): bool
    {
        clearstatcache();

        $path = $this->path($key);
        if (file_exists($path) === false) {
            return false;
        }

        $time = filemtime($path);
        if ($time < time()) {
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
    public function get(string $key): mixed
    {
        if ($this->has($key) === false) {
            return false;
        }

        $path = $this->path($key);
        $data = file_get_contents($path);
        if ($data === false) {
            return false;
        }

        $data = unserialize($data);
        return $data;
    }

    /**
     * @return true
     */
    public function clear(): true
    {
        // @todo
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
        clearstatcache();

        $path = $this->path($key);
        if (file_exists($path) === false) {
            return false;
        }

        return unlink($path);
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
        $path = $this->path($key);
        $data = serialize($data);

        return file_put_contents($path, $data) && touch($path, $this->ttl);
    }
}
