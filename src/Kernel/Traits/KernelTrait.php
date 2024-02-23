<?php

namespace Solital\Core\Kernel\Traits;

use Solital\Core\Kernel\Application;

trait KernelTrait
{
    /**
     * That variables must be changed manually
     */
    const SOLITAL_VERSION   = "4.1.0";
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
}
