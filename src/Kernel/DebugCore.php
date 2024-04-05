<?php

namespace Solital\Core\Kernel;

abstract class DebugCore
{
    /**
     * @var bool
     */
    private static bool $debug = false;

    /**
     * @var array
     */
    private static array $db = [];

    /**
     * @var array
     */
    private static array $mail_config = [];

    /**
     * Enable debug in Core
     *
     * @return void
     */
    public static function enableCoreDebug(): void
    {
        self::$debug = true;
    }

    /**
     * Get debug status
     *
     * @return bool
     */
    public static function isCoreDebugEnabled(): bool
    {
        return self::$debug;
    }

    /**
     * Set database connection for debug Core
     *
     * @param string $drive
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $pass
     * @param string $sqlite
     * 
     * @return void
     */
    public static function setDatabaseConnection(
        string $drive,
        string $host,
        string $dbname,
        string $user,
        string $pass = '',
        string $sqlite = ''
    ): void {
        self::$db['drive']      = $drive;
        self::$db['host']       = $host;
        self::$db['name']       = $dbname;
        self::$db['user']       = $user;
        self::$db['pass']       = $pass;
        self::$db['sqlite_dir'] = $sqlite;
    }

    /**
     * Get database connection to debug Core
     *
     * @return array
     */
    public static function getDatabaseConnection(): array
    {
        return self::$db;
    }

    /**
     * Set mailer configuration to debug Core
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $secure
     * @param int $port
     * 
     * @return void
     */
    public static function setMailConfig(
        ?string $host = null,
        ?string $user = null,
        ?string $pass = null,
        ?string $secure = 'tls',
        ?int $port = 587
    ): void {
        self::$mail_config['host']       = $host;
        self::$mail_config['user']       = $user;
        self::$mail_config['pass']       = $pass;
        self::$mail_config['security']   = $secure;
        self::$mail_config['port']       = $port;
    }

    /**
     * Get mailer configuration to debug Core
     *
     * @return array
     */
    public static function getMailConfig(): array
    {
        return self::$mail_config;
    }
}
