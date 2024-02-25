<?php

namespace Solital\Core\Kernel\Traits;

use ModernPHPException\ModernPHPException;
use Solital\Core\Kernel\Application;

trait KernelTrait
{
    /**
     * That variables must be changed manually
     */
    const SOLITAL_VERSION   = "4.1.1";
    const SITE_DOC_DOMAIN   = "https://solital.github.io/site/";
    const DEBUG             = false;
    const DEBUG_DATABASE    = false;
    const MAILER_TEST_UNIT  = false;

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
     * @return ModernPHPException
     */
    public static function startModernPHPException(string $config_file = ''): ModernPHPException
    {
        $exception = new ModernPHPException($config_file);

        if (self::fileExistsWithoutCache($config_file) && $config_file != '') {
            $yaml_data = Application::yamlParse('exceptions.yaml');

            if (array_key_exists('enable_occurrences', $yaml_data) && $yaml_data['enable_occurrences'] == true) {
                $exception->enableOccurrences();
            }
        }

        return $exception->start();
    }
}
