<?php

namespace Solital\Core\Kernel;

use Solital\Core\Kernel\Application;

trait KernelTrait
{
    /**
     * That variables must be changed manually
     */
    const SOLITAL_VERSION   = "3.7.0";
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
        } else {
            return null;
        }
    }
}
