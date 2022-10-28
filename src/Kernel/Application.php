<?php

namespace Solital\Core\Kernel;

use ModernPHPException\ModernPHPException;
use Symfony\Component\Yaml\Yaml;
use Solital\Core\Course\Course;
use Solital\Core\Exceptions\ApplicationException;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\KernelTrait;
use Solital\Core\Resource\Session;
use Solital\Core\Security\Guardian;
use Solital\Core\Validation\Convertime;

class Application
{
    use KernelTrait;

    const SOLITAL_VERSION = "3.2.0";
    const SITE_DOC_DOMAIN = "http://solitalframework.rf.gd/";

    /**
     * This variable must be changed manually
     */
    const DEBUG = false;

    /**
     * The `connectionDatabaseDebug` method must be edited manually
     */
    const DEBUG_DATABASE = false;

    /**
     * This variable must be changed manually
     */
    const MAILER_TEST_UNIT = false;

    /**
     * @var string
     */
    private static string $error_message;

    /**
     * @var int
     */
    private static int $error_code;

    /**
     * @var ModernPHPException
     */
    private static ModernPHPException $exception;

    /**
     * @var HandleFiles
     */
    private static HandleFiles $handle;

    /**
     * @param int $dir_number
     * 
     * @return string
     */
    public static function getDirConfigFiles(int $dir_number): string
    {
        if (self::DEBUG == true) {
            $dir_config_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;
        } else {
            $dir_config_file = dirname(__DIR__, $dir_number) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        }

        return $dir_config_file;
    }

    /**
     * @param int $dir_number
     * @param string $yaml_file
     * 
     * @return mixed
     */
    public static function getYamlVariables(int $dir_number, string $yaml_file): mixed
    {
        return Yaml::parseFile(self::getDirConfigFiles($dir_number) . $yaml_file);
    }

    /**
     * @return void
     */
    private static function getInstance(): void
    {
        $exception_type = "";
        $exception_theme = null;

        if (self::DEBUG == false) {
            if (file_exists(self::getDirConfigFiles(5) . 'bootstrap.yaml')) {
                $modern_php_exception = self::getYamlVariables(5, 'bootstrap.yaml');

                if (array_key_exists('exception_json_return', $modern_php_exception)) {
                    if ($modern_php_exception['exception_json_return'] == true) {
                        $exception_type = "json";
                    }
                }

                if (array_key_exists('exception_dark_theme', $modern_php_exception)) {
                    $exception_theme = $modern_php_exception['exception_dark_theme'];
                }
            }
        }

        self::$exception = new ModernPHPException([
            'type' => $exception_type,
            'title' => '',
            'dark_mode' => $exception_theme,
            'production_mode' => getenv('PRODUCTION_MODE')
        ]);
        self::$exception->start();

        self::$handle = new HandleFiles();
    }

    /**
     * @return void
     */
    public static function init(): void
    {
        (new Convertime());
        self::getInstance();
        self::connectionDatabase();
        self::protectDomain();
        Course::start();
    }

    /**
     * @param \Exception $e
     * 
     * @return void
     */
    public static function exceptionHandler(\Exception $e): void
    {
        self::$exception->exceptionHandler($e);
    }

    /**
     * @return mixed
     */
    public static function connectionDatabase(): mixed
    {
        if (self::DEBUG_DATABASE == true) {
            return self::connectionDatabaseDebug();
        } else {
            $database_connection = self::getYamlVariables(5, 'database.yaml');
        }

        if ($database_connection['enable_test'] == true) {
            if (!defined('DB_CONFIG')) {
                define('DB_CONFIG', [
                    'DRIVE' => $database_connection['db_test']['drive'],
                    'HOST' => $database_connection['db_test']['host'],
                    'DBNAME' => $database_connection['db_test']['name'],
                    'USER' => $database_connection['db_test']['user'],
                    'PASS' => $database_connection['db_test']['pass'],
                    'SQLITE_DIR' => $database_connection['db_test']['sqlite']
                ]);
            }
        } else {
            if (!defined('DB_CONFIG')) {
                define('DB_CONFIG', [
                    'DRIVE' => getenv('DB_DRIVE'),
                    'HOST' => getenv('DB_HOST'),
                    'DBNAME' => getenv('DB_NAME'),
                    'USER' => getenv('DB_USER'),
                    'PASS' => getenv('DB_PASS'),
                    'SQLITE_DIR' => getenv('SQLITE_DIR')
                ]);
            }
        }

        return __CLASS__;
    }

    /**
     * @return void
     */
    private static function connectionDatabaseDebug(): void
    {
        if (!defined('DB_CONFIG')) {
            define('DB_CONFIG', [
                'DRIVE' => '',
                'HOST' => '',
                'DBNAME' => '',
                'USER' => '',
                'PASS' => '',
                'SQLITE_DIR' => ''
            ]);
        }
    }

    /**
     * @param string $dir
     * 
     * @return string
     * @throws Exception
     */
    public static function getRoot(string $dir = "", ?bool $cli_test = false): string
    {
        self::getInstance();

        if ($cli_test == true) {
            return self::getRootTest($dir);
        }

        if (defined('SITE_ROOT')) {
            if ($dir != "" || !empty($dir)) {
                $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
                $dir = $dir . DIRECTORY_SEPARATOR;
            }

            return constant('SITE_ROOT') . DIRECTORY_SEPARATOR . $dir;
        } else {
            throw new ApplicationException("SITE_ROOT constant not defined");
        }
    }

    /**
     * @param string $dir
     * @param bool $cli_test
     * 
     * @return string
     * @throws Exception
     */
    public static function getRootApp(string $dir, ?bool $cli_test = false): string
    {
        self::getInstance();

        if ($cli_test == true) {
            return self::getRootTest($dir);
        }

        if (defined('SITE_ROOT')) {
            $dir_app = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR;

            if (!is_dir($dir_app . $dir)) {
                self::$handle->create($dir_app . $dir);
            }

            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            return $dir_app . $dir;
        } else {
            throw new ApplicationException("SITE_ROOT constant not defined");
        }
    }

    /**
     * @param string $dir
     * 
     * @return string
     */
    public static function getRootTest(string $dir = ""): string
    {
        self::getInstance();

        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        $dir = "tests" . DIRECTORY_SEPARATOR . "files_test" . DIRECTORY_SEPARATOR . $dir;

        if (!is_dir($dir)) {
            self::$handle->create($dir);
        }

        return $dir;
    }

    /**
     * @param string $dir
     * 
     * @return string
     */
    public static function getRootCore(string $dir = ""): string
    {
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        $dir_core = dirname(__DIR__);

        return $dir_core . $dir;
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
     * Recursively loads all php files in all subdirectories of the given path
     *
     * @param $directory
     *
     * @throws \Exception
     */
    public static function autoload($directory)
    {
        // Ensure this path exists
        if (!is_dir($directory)) {
            return;
        }

        // Get a listing of the current directory
        $scanned_dir = scandir($directory);

        // Ignore these items from scandir
        $ignore = [
            '.',
            '..'
        ];

        // Remove the ignored items
        $scanned_dir = array_diff($scanned_dir, $ignore);

        if (empty($scanned_dir)) {
            return;
        }

        if (count($scanned_dir) > 250) {
            throw new \Exception('Too many files attempted to load via autoload');
        }

        foreach ($scanned_dir as $item) {

            $filename  = $directory . '/' . $item;
            $real_path = realpath($filename);

            if (false === $real_path) {
                continue;
            }

            $filetype = filetype($real_path);

            if (empty($filetype)) {
                continue;
            }

            // If it's a directory then recursively load it
            if ('dir' === $filetype) {
                self::autoload($real_path);
            } // If it's a file, let's try to load it
            else if ('file' === $filetype) {

                if (true !== is_readable($real_path)) {
                    continue;
                }

                // Don't allow files that have been uploaded
                if (is_uploaded_file($real_path)) {
                    continue;
                }

                // Only for files that really exist
                if (true !== file_exists($real_path)) {
                    continue;
                }

                $pathinfo = pathinfo($real_path);

                // An empty filename wouldn't be a good idea
                if (empty($pathinfo['filename'])) {
                    continue;
                }

                // Sorry, need an extension
                if (empty($pathinfo['extension'])) {
                    continue;
                }

                // Actually, we want just a PHP extension!
                if ('php' !== $pathinfo['extension']) {
                    continue;
                }

                $filesize = filesize($real_path);

                // Don't include negative sized files
                if ($filesize < 0) {
                    throw new \Exception('File size is negative, not autoloading');
                }

                // Don't include files that are greater than 300kb
                if ($filesize > 300000) {
                    throw new \Exception('File size is greater than 300kb, not autoloading');
                }

                require_once($real_path);
            }
        }
    }

    /**
     * @return void
     */
    public static function protectDomain(): void
    {
        Guardian::validateDomain();
    }

    /**
     * @return void
     */
    public static function sessionInit(): void
    {
        $session_dir = dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "session" . DIRECTORY_SEPARATOR;

        if (!session_id()) {
            if (!is_dir($session_dir)) {
                (new HandleFiles)->create($session_dir);
            }

            session_save_path($session_dir);
            Session::start();
        }
    }

    /**
     * @return mixed
     */
    public static function appStatus(): mixed
    {
        $config = true;
        $message = [];
        $theme_dark = null;

        if (
            empty(getenv('DB_DRIVE')) ||
            empty(getenv('DB_HOST')) ||
            empty(getenv('DB_NAME')) ||
            empty(getenv('DB_USER')) ||
            empty(getenv('DB_DRIVE'))
        ) {
            $config = false;
            $message[] = "<span class='alert'>Database not configured:</span> check '.env' file";
        }

        if (empty(getenv('FIRST_SECRET')) || empty(getenv('SECOND_SECRET'))) {
            $config = false;
            $message[] = "<span class='alert'>OpenSSL error:</span> FIRST_SECRET and SECOND_SECRET variables don't have a defined value";
        }

        if (date('H') >= 18) {
            $theme_dark = 'dark';
        } else {
            $theme_dark = '';
        }

        return [
            'status' => $config,
            'message' => $message,
            'theme_dark' => $theme_dark
        ];
    }
}
