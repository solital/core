<?php

namespace Solital\Core\Kernel;

use Solital\Core\Kernel\Application;

/**
 * 
 */
trait KernelTrait
{
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
