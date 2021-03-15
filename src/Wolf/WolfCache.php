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
    protected static $cache_dir = ROOT . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "solital" . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Cache" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;

    /**
     * @var date
     */
    protected static $time;

    /**
     * @param date $time
     */
    public static function cache($time)
    {
        self::$time = $time;

        return self::$time;
    }
}
