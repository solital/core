<?php

namespace Solital\Core\Kernel\Traits;

use Solital\Core\Kernel\DebugCore;

trait DatabaseTrait
{
    use KernelTrait;

    /**
     * @var array
     */
    protected static array $db = [
        'drive'      => '',
        'host'       => '',
        'name'       => '',
        'user'       => '',
        'pass'       => '',
        'sqlite_dir' => ''
    ];

    /**
     * Set database connection using .env file or database.yaml file
     * 
     * @param array $database_connection
     * 
     * @return array
     */
    protected static function setDatabaseConnection(array $database_connection): array
    {
        if (DebugCore::isCoreDebugEnabled()) {
            self::$db = DebugCore::getDatabaseConnection();
            return self::$db;
        }

        if ($database_connection['enable_test'] == true) {
            self::$db['drive']      = $database_connection['db_test']['drive'];
            self::$db['host']       = $database_connection['db_test']['host'];
            self::$db['name']       = $database_connection['db_test']['name'];
            self::$db['user']       = $database_connection['db_test']['user'];
            self::$db['pass']       = $database_connection['db_test']['pass'];
            self::$db['sqlite_dir'] = $database_connection['db_test']['sqlite'];

            return self::$db;
        }

        self::$db['drive']      = getenv('DB_DRIVE');
        self::$db['host']       = getenv('DB_HOST');
        self::$db['name']       = getenv('DB_NAME');
        self::$db['user']       = getenv('DB_USER');
        self::$db['pass']       = getenv('DB_PASS');
        self::$db['sqlite_dir'] = getenv('SQLITE_DIR');

        return self::$db;
    }

    /**
     * Set database cache for Katrina
     *
     * @return void
     */
    public static function loadDatabaseCache(): void
    {
        $database_cache = self::yamlParse('cache.yaml');

        if (isset($database_cache['cache_database']) && $database_cache['cache_database'] == true) {
            if ($database_cache['cache_drive'] == 'file') {
                throw new \TypeError('The cache drive "file" is not supported by Katrina ORM. Use "memcached", "memcache" or "apcu"');
            }

            if (!defined('DB_CACHE')) {
                define('DB_CACHE', [
                    'CACHE_TYPE' => $database_cache['cache_drive'],
                    'CACHE_HOST' => $database_cache['cache_host'],
                    'CACHE_PORT' => $database_cache['cache_port'],
                    'CACHE_TTL' => $database_cache['cache_ttl']
                ]);
            }
        }
    }
}
