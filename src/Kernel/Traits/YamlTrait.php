<?php

namespace Solital\Core\Kernel\Traits;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Kernel\DebugCore;
use Solital\Core\Kernel\Exceptions\{ApplicationException, YamlException};

trait YamlTrait
{
    /**
     * Returns variables by YAML file
     *
     * @param string $yaml_file       YAML file name
     * @param bool   $return_dir_name If TRUE, get directory of the YAML files
     * 
     * @return mixed
     * @throws ApplicationException
     */
    public static function yamlParse(
        string $yaml_file,
        bool $return_dir_name = false,
        bool $throws = false
    ): mixed {
        if (DebugCore::isCoreDebugEnabled() == true) {
            $yaml_dir_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Kernel' . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;
        } else {
            if (!defined('SITE_ROOT')) throw new ApplicationException("SITE_ROOT constant not defined");
            $yaml_dir_file = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        }

        $full_yaml_file = $yaml_dir_file  . $yaml_file;

        if (!self::fileExistsWithoutCache($full_yaml_file)) {
            if ($throws == true) throw new YamlException('YAML file "' . $yaml_file . '" not found');
            return false;
        }

        if ($return_dir_name == true) return $full_yaml_file;

        return Yaml::parseFile($full_yaml_file);
    }

    /**
     * Add a value on YAML file
     *
     * @param string $yaml_file
     * @param string $key
     * @param string $value
     * 
     * @return true
     */
    public static function addYamlValue(string $yaml_file, string $key, string $value): true
    {
        $yaml_file_dir = self::yamlParse($yaml_file, true);
        $file = fopen($yaml_file_dir, "a+");

        if (!$file) {
            throw new YamlException("Failed to open '" . $yaml_file . "' file");
        }

        fwrite($file, "\n" . $key . ': ' . $value);

        while (($line = fgets($file)) !== false) {
            echo $line;
        }

        if (!feof($file)) {
            throw new YamlException("fgets() unexpected failure");
        }

        fclose($file);
        return true;
    }
}
