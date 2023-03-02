<?php

namespace Solital\Core\Cache\Psr6;

use Solital\Core\Kernel\Application;

abstract class CacheDir
{
    const NO_VALID_CACHE_DIR = 'The cache directory setted not exist.';

    /**
     * @var null|string
     */
    protected static $cache_dir;

    /**
     * Return the cache path.
     * 
     * @return mixed
     */
    public static function getCacheDir()
    {
        return self::$cache_dir !== null ? self::$cache_dir : self::setDefaultCacheDir();
    }

    /**
     * Set cache dir.
     * 
     * @param mixed $cache_dir
     * 
     * @return mixed
     */
    public static function setCacheDir($cache_dir)
    {
        if ($cache_dir == true) {
            return self::$cache_dir = Application::getRootApp("Storage/cache/");
        }

        if (empty($cache_dir)) {
            return false;
        }

        $defined_cacheDir = substr($cache_dir, -1) == '/' ? $cache_dir : ($cache_dir . '/');
            
        if (!is_dir($cache_dir)) {
            throw new \Exception(self::NO_VALID_CACHE_DIR);
        }

        return self::$cache_dir = $defined_cacheDir;
    }

    /**
     * Set default cache dir.
     * 
     * @return mixed
     */
    public static function setDefaultCacheDir()
    {
        return self::$cache_dir = Application::getRootApp("Storage/cache/");
    }

    /**
     * Reset the cache dir setted.
     */
    public static function resetCacheDir()
    {
        self::$cache_dir = null;
    }
}
