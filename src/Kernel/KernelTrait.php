<?php

namespace Solital\Core\Kernel;

use Solital\Core\Kernel\Application;

/**
 * 
 */
trait KernelTrait
{
    const SOLITAL_VERSION = "3.4.0";
    const SITE_DOC_DOMAIN = "http://solitalframework.rf.gd/";

    /**
     * This variable must be changed manually
     */
    const DEBUG = true;

    /**
     * The `connectionDatabaseDebug` method must be edited manually
     */
    const DEBUG_DATABASE = true;

    /**
     * This variable must be changed manually
     */
    const MAILER_TEST_UNIT = false;
    
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
        } else {
            return null;
        }
    }
}
