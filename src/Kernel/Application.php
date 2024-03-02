<?php

namespace Solital\Core\Kernel;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Course\Course;
use Solital\Core\Resource\Session;
use Solital\Core\Security\Guardian;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Exceptions\{ApplicationException, YamlException};

use Solital\Core\Kernel\Traits\{
    KernelTrait,
    ClassLoaderTrait,
    DatabaseTrait
};

use Solital\Core\Container\{
    Interface\ContainerInterface,
    Container,
    DefaultServiceContainer
};

abstract class Application
{
    use KernelTrait;
    use ClassLoaderTrait;
    use DatabaseTrait;

    /**
     * @var string
     */
    private static string $error_message;

    /**
     * @var int
     */
    private static int $error_code;

    /**
     * @var string
     */
    private static string $default_timezone = "America/Fortaleza";

    /**
     * @var HandleFiles
     */
    private static HandleFiles $handle;

    /**
     * @var mixed
     */
    private static mixed $container;

    /**
     * Returns variables by YAML file
     *
     * @param string $yaml_file       YAML file name
     * @param bool   $return_dir_name If TRUE, get directory of the YAML files
     * 
     * @return mixed
     * @throws ApplicationException
     */
    public static function yamlParse(string $yaml_file, bool $return_dir_name = false, bool $throws = false): mixed
    {
        if (self::DEBUG == true) {
            $yaml_dir_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;
        } else {
            if (!defined('SITE_ROOT')) {
                throw new ApplicationException("SITE_ROOT constant not defined");
            }

            $yaml_dir_file = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        }

        if (!self::fileExistsWithoutCache($yaml_dir_file)) {
            if ($throws == true) {
                throw new YamlException('YAML file "' . $yaml_file . '" not found');
            }

            return false;
        }

        if ($return_dir_name == true) {
            return $yaml_dir_file  . $yaml_file;
        }

        return Yaml::parseFile($yaml_dir_file . $yaml_file);
    }

    /**
     * Get directory of the YAML files
     * 
     * @deprecated Use Application::yamlParse()
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
     * Returns variables by YAML file
     * 
     * @deprecated Use Application::yamlParse()
     * @param int $dir_number
     * @param string $yaml_file
     * 
     * @return mixed
     */
    public static function getYamlVariables(int $dir_number, string $yaml_file): mixed
    {
        $yaml_file = self::getDirConfigFiles($dir_number) . $yaml_file;

        if (file_exists($yaml_file)) {
            return Yaml::parseFile($yaml_file);
        }

        return false;
    }

    /**
     * Initiate instance project
     * 
     * @return void
     */
    public static function getInstance(): void
    {
        /* START MODERN PHP EXCEPTION WITHOUT CONFIG FILE*/
        $exception_instance = self::startModernPHPException();

        /* LOAD YAML CONFIG */
        $exception_config = self::yamlParse('exceptions.yaml', true);
        $bootstrap_config = self::yamlParse('bootstrap.yaml');

        /* LOAD SERVICE PROVIDER */
        self::$container = new Container();
        self::loadServiceContainer(self::$container);

        /* LOAD DEFAULT TIMEZONE */
        date_default_timezone_set(self::$default_timezone);

        if (is_array($bootstrap_config)) {
            if (array_key_exists('default_timezone', $bootstrap_config) && $bootstrap_config['default_timezone'] != "") {
                date_default_timezone_set($bootstrap_config['default_timezone']);
            }
        }

        /* IF CONFIG FILE EXISTS, UNSET OLD INSTANCE AND CREATE NEW MODERN PHP EXCEPTION INSTANCE */
        if (self::fileExistsWithoutCache($exception_config)) {
            unset($exception_instance);
            self::startModernPHPException($exception_config);
        }

        /* LOAD PROVIDER HANDLE FILES */
        self::$handle = self::provider('handler-file');
    }

    /**
     * Init all Solital instances, database connection, security methods and
     * start all routers
     * 
     * @return void
     */
    public static function init(): void
    {
        self::getInstance();
        self::connectionDatabase();
        Guardian::validateDomain();

        Course::start();
    }

    /**
     * Get container ID
     *
     * @param string $provider
     * 
     * @return mixed
     */
    public static function provider(string $provider): mixed
    {
        return self::$container->get($provider);
    }

    /**
     * Set database connection constants
     * 
     * @return void
     */
    public static function connectionDatabase(): void
    {
        $db_config = self::$db;
        $database_connection = self::yamlParse('database.yaml', throws: true);
        $db_config = self::setDatabaseConnection($database_connection);

        // Set variables fo main database
        if (!defined('DB_CONFIG')) {
            define('DB_CONFIG', [
                'DRIVE'      => $db_config['drive'],
                'HOST'       => $db_config['host'],
                'DBNAME'     => $db_config['name'],
                'USER'       => $db_config['user'],
                'PASS'       => $db_config['pass'],
                'SQLITE_DIR' => $db_config['sqlite_dir']
            ]);
        }

        // Set variables of secondary database
        if (
            getenv('DB_HOST_SECONDARY') != null ||
            getenv('DB_NAME_SECONDARY') != null ||
            getenv('DB_USER_SECONDARY') != null ||
            getenv('DB_PASS_SECONDARY') != null ||
            getenv('SQLITE_DIR_SECONDARY') != null
        ) {
            if (!defined('DB_CONFIG_SECONDARY')) {
                define('DB_CONFIG_SECONDARY', [
                    'HOST'       => getenv('DB_HOST_SECONDARY'),
                    'DBNAME'     => getenv('DB_NAME_SECONDARY'),
                    'USER'       => getenv('DB_USER_SECONDARY'),
                    'PASS'       => getenv('DB_PASS_SECONDARY'),
                    'SQLITE_DIR' => getenv('SQLITE_DIR_SECONDARY')
                ]);
            }
        }

        self::loadDatabaseCache();
    }

    /**
     * Return directory on root folder
     * 
     * @param string $dir
     * 
     * @return string
     * @throws Exception
     */
    public static function getRoot(string $dir = "", ?bool $cli_test = false): string
    {
        self::getInstance();

        if ($cli_test == true) {
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            return "tests" . DIRECTORY_SEPARATOR . "files_test" . DIRECTORY_SEPARATOR . $dir;
        }

        if (defined('SITE_ROOT')) {
            if ($dir != "" || !empty($dir)) {
                $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
                $dir = $dir . DIRECTORY_SEPARATOR;
            }

            return constant('SITE_ROOT') . DIRECTORY_SEPARATOR . $dir;
        }

        throw new ApplicationException("SITE_ROOT constant not defined");
    }

    /**
     * Return directory in `app/` folder
     * 
     * @param string $dir
     * @param bool|null $cli_test
     * @param bool|null $create_app_folder
     * 
     * @return string
     * @throws ApplicationException
     */
    public static function getRootApp(string $dir, ?bool $create_app_folder = true): string
    {
        self::getInstance();

        if (self::DEBUG == true) {
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            $dir_app = "tests" . DIRECTORY_SEPARATOR . "files_test" . DIRECTORY_SEPARATOR;
        } else {
            if (defined('SITE_ROOT')) {
                $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
                $dir_app = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR;
            } else {
                throw new ApplicationException("SITE_ROOT constant not defined");
            }
        }

        if ($create_app_folder == true) {
            self::createAppFolder($dir_app . $dir);
        }

        return $dir_app . $dir;
    }

    /**
     * createAppFolder
     *
     * @param  mixed $directory
     * @return void
     */
    public static function createAppFolder(string $directory)
    {
        self::getInstance();

        if (!is_dir($directory)) {
            self::$handle->create($directory);
        }
    }

    /**
     * removeAppFolder
     * 
     * @param  mixed $directory
     * @return void
     */
    /* public static function removeAppFolder(string $directory)
    {
        if (is_dir($directory)) {
            self::$handle->remove($directory, false);
        }
    } */

    /**
     * Return folder on Core
     * 
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
     * Start session
     * 
     * @return void
     */
    public static function sessionInit(): void
    {
        $session_dir = self::getRootApp('Storage/session');

        if (!session_id()) {
            if (!is_dir($session_dir)) {
                self::$handle->create($session_dir);
            }

            session_save_path($session_dir);
            Session::start();
        }
    }

    /**
     * Load CSRF verifier
     *
     * @return void
     */
    public static function loadCsrfVerifier(): void
    {
        $custom_csrf = self::yamlParse('bootstrap.yaml', throws: true);

        $class = 'Solital\Core\Http\Middleware\\' . $custom_csrf['custom_csrf'];

        if (!class_exists($class)) {
            $class = 'Solital\Middleware\\' . $custom_csrf['custom_csrf'];
        }

        $reflection = new \ReflectionClass($class);
        $instance = $reflection->newInstance();

        Course::csrfVerifier($instance);
    }

    /**
     * Load service provider class and default class
     *
     * @param ContainerInterface $container
     * 
     * @return void
     */
    public static function loadServiceContainer(ContainerInterface $container): void
    {
        if (class_exists('Solital\ServiceContainer')) {
            $service = new \Solital\ServiceContainer;
            $service->register($container);
        }

        $default_service = new DefaultServiceContainer;
        $default_service->register($container);
    }

    /**
     * Check app status
     * 
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
            $message['Database not configured'] = "Check database configuration at '.env' file";
        }

        if (
            empty(getenv('MAIL_HOST')) ||
            empty(getenv('MAIL_USER')) ||
            empty(getenv('MAIL_PASS')) ||
            empty(getenv('MAIL_SECURITY')) ||
            empty(getenv('MAIL_PORT'))
        ) {
            $config = false;
            $message['E-mail not configured'] = "Check e-mail configuration at '.env' file";
        }

        if (empty(getenv('FIRST_SECRET')) || empty(getenv('SECOND_SECRET'))) {
            $config = false;
            $message['OpenSSL error'] = "FIRST_SECRET and SECOND_SECRET variables don't have a defined value";
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
