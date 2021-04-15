<?php

namespace Solital\Core\Wolf;

abstract class WolfCache
{
    /**
     * @var string
     */
    protected static $file_cache;

    /**
     * @var string
     */
    protected static $cache_dir;

    /**
     * @var date
     */
    protected static $time;

    /**
     * @return string
     */
    protected static function getFolderCache()
    {
        self::$cache_dir = SITE_ROOT . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "wolf" . DIRECTORY_SEPARATOR;

        if (!is_dir(self::$cache_dir)) {
            \mkdir(self::$cache_dir);
        }

        return self::$cache_dir;
    }

    /**
     * @return static
     */
    public static function cache()
    {
        return new static();
    }

    /**
     * @return string
     */
    public function forOneMinute(): string
    {
        self::$time = date('Hi');

        return self::$time;
    }

    /**
     * @return string
     */
    public function forOneHour(): string
    {
        self::$time = date('H');

        return self::$time;
    }

    /**
     * @return string
     */
    public function forOneDay(): string
    {
        self::$time = date('N');

        return self::$time;
    }

    /**
     * @return string
     */
    public function forOneWeek(): string
    {
        self::$time = date('W');

        return self::$time;
    }
}
