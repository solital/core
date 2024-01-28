<?php

namespace Solital\Core\Kernel\Traits;

use Solital\Core\Kernel\Application;

trait KernelTrait
{
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
     * That variables must be changed manually
     */
    const SOLITAL_VERSION   = "4.0.1";
    const SITE_DOC_DOMAIN   = "https://solital.github.io/site/";
    const DEBUG             = false;
    const DEBUG_DATABASE    = false;
    const MAILER_TEST_UNIT  = false;

    /**
     * @param string $component_name
     * 
     * @return null|string
     */
    public static function getConsoleComponent(string $component_name): ?string
    {
        $component_file = Application::getRootCore('/Kernel/Console/Templates/' . $component_name);

        if (file_exists($component_file)) {
            return $component_file;
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function isCli(): bool
    {
        if (defined('STDIN')) {
            return true;
        }

        if (php_sapi_name() === "cli") {
            return true;
        }

        if (PHP_SAPI === 'cli') {
            return true;
        }

        if (stristr(PHP_SAPI, 'cgi') and getenv('TERM')) {
            return true;
        }

        if (
            empty($_SERVER['REMOTE_ADDR']) and
            !isset($_SERVER['HTTP_USER_AGENT']) and
            count($_SERVER['argv']) > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param array $database_connection
     * 
     * @return array
     */
    protected static function setDatabaseConnection(array $database_connection): array
    {
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
}
