<?php

namespace Solital\Core\Kernel\Traits;

use ModernPHPException\ModernPHPException;
use Solital\Core\Security\Hash;
use Solital\Core\Kernel\Exceptions\DotenvException;
use Solital\Core\Kernel\{Application, DebugCore, Dotenv};

trait KernelTrait
{
    /**
     * That variables must be changed manually
     */
    const SOLITAL_VERSION   = "4.8.1";
    const SITE_DOC_DOMAIN   = "https://solital.github.io/site/";

    /**
     * Get an component template on Kernel folder
     * 
     * @param string $component_name
     * 
     * @return null|string
     */
    public static function getConsoleComponent(string $component_name): ?string
    {
        $component_file = Application::getRootCore('/Kernel/Console/Templates/' . $component_name);
        if (file_exists($component_file)) return $component_file;
        return null;
    }

    /**
     * Check if is executed in CLI
     * 
     * @return bool
     */
    public static function isCli(): bool
    {
        if (defined('STDIN')) return true;
        if (php_sapi_name() === "cli") return true;
        if (PHP_SAPI === 'cli') return true;
        if (stristr(PHP_SAPI, 'cgi') and getenv('TERM')) return true;

        if (
            empty($_SERVER['REMOTE_ADDR']) and
            !isset($_SERVER['HTTP_USER_AGENT'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether a file or directory exists without storage results on cache
     *
     * @param string $file_path
     * 
     * @return bool
     */
    public static function fileExistsWithoutCache(string $file_path): bool
    {
        $file_exists = false;

        //clear cached results
        clearstatcache(true, $file_path);

        //trim path
        $file_dir = trim(dirname($file_path));

        //normalize path separator
        $file_dir = str_replace('/', DIRECTORY_SEPARATOR, $file_dir) . DIRECTORY_SEPARATOR;

        //trim file name
        $file_name = trim(basename($file_path));

        //rebuild path
        $file_path = $file_dir . "{$file_name}";

        //If you simply want to check that some file (not directory) exists, 
        //and concerned about performance, try is_file() instead.
        //It seems like is_file() is almost 2x faster when a file exists 
        //and about the same when it doesn't.

        //$file_exists = is_file($file_path);
        $file_exists = file_exists($file_path);
        return $file_exists;
    }

    /**
     * Start Modern PHP Exception
     *
     * @param string $config_file
     * 
     * @return null|ModernPHPException
     */
    public static function startModernPHPException(string $config_file = ''): ?ModernPHPException
    {
        if (DebugCore::isCoreDebugEnabled() === true) return null;
        $exception = new ModernPHPException($config_file);

        if (self::fileExistsWithoutCache($config_file) && $config_file != '') {
            $yaml_data = Application::yamlParse('exceptions.yaml');

            if (array_key_exists('enable_occurrences', $yaml_data) && $yaml_data['enable_occurrences'] == true) {
                $exception->enableOccurrences();
            }

            if (array_key_exists('ignore_errors', $yaml_data) && $yaml_data["ignore_errors"] != []) {
                $errors = [];

                foreach ($yaml_data["ignore_errors"] as $error) {
                    $errors[] = constant($error);
                }
                //dump_die($errors);
                $exception->ignoreErrors($errors);
            }
        }

        if (DebugCore::isCoreDebugEnabled() === true) {
            restore_error_handler();
            restore_exception_handler();
        }

        return $exception->start();
    }

    /**
     * Verify for APP_HASH variable
     *
     * @return void
     */
    public static function verifyAppHash(): void
    {
        if (DebugCore::isCoreDebugEnabled() == false) {
            if (!Dotenv::isset('APP_HASH')) {
                try {
                    Dotenv::add('APP_HASH', Hash::randomString());
                } catch (DotenvException) {
                    throw new DotenvException("APP_HASH not found. Execute 'php vinci generate:hash' command");
                }
            }

            if (Dotenv::isset('APP_HASH') && empty(getenv('APP_HASH'))) {
                try {
                    Dotenv::edit('APP_HASH', Hash::randomString());
                } catch (DotenvException) {
                    throw new DotenvException("APP_HASH not found. Execute 'php vinci generate:hash' command");
                }
            }
        }
    }

    /**
     * Check if composer autoload exists
     *
     * @return void
     */
    private static function composerExists(): void
    {
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

        if (self::fileExistsWithoutCache($autoload)) {
            echo 'Could not find "autoload.php". Did you run "composer update"?' . PHP_EOL;
            exit(1);
        }
    }
}
