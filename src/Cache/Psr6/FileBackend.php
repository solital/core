<?php

namespace Solital\Core\Cache\Psr6;

use Solital\Core\Kernel\Application;

class FileBackend
{
    /**
     * @var string
     */
    private string $directory;

    /**
     * @param string $directory
     */
    public function __construct(string $directory = "")
    {
        if ($directory == "" || is_null($directory)) {
            $directory = Application::getRootApp("Storage/cache/");
        }

        $this->directory = $directory;
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
     * @param int $time
     * 
     * @return mixed
     */
    public function save(string $key, mixed $data, int $time): mixed
    {
        $path = $this->path($key);
        $data = serialize($data);

        return file_put_contents($path, $data) && touch($path, $time);
    }
}
