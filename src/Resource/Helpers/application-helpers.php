<?php

use Solital\Core\Kernel\Application;

/**
 * Returns variables by YAML file
 *
 * @param string $yaml_file
 * @param bool $return_dir_name
 * @param bool $throws
 * 
 * @return mixed
 */
function app_get_yaml(string $yaml_file, bool $return_dir_name = false, bool $throws = false): mixed
{
    return Application::yamlParse($yaml_file, $return_dir_name, $throws);
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
function add_add_yaml(string $yaml_file, string $key, string $value): true
{
    return Application::addYamlValue($yaml_file, $key, $value);
}

/**
 * Get Solital's database connection
 *
 * @return void
 */
function app_get_database_connection(): void
{
    Application::connectionDatabase();
}

/**
 * Return directory on root folder
 *
 * @param string $dir
 * @param bool|null $cli_test
 * 
 * @return string
 */
function app_get_root(string $dir = "", ?bool $cli_test = false): string
{
    return Application::getRoot($dir, $cli_test);
}

/**
 * Return directory in `app/` folder
 *
 * @param string $dir
 * @param bool|null $create_app_folder
 * 
 * @return string
 */
function app_get_app(string $dir, ?bool $create_app_folder = true): string
{
    return Application::getRootApp($dir, $create_app_folder);
}

/**
 * Recursively loads all php files in all subdirectories of the given path
 *
 * @param string $directory
 * 
 * @return true|null
 */
function app_autoload(string $directory): ?true
{
    return Application::autoload($directory);
}

/**
 * Load class in directory
 *
 * @param string $directory
 * 
 * @return array
 */
function app_classloader(string $directory): array
{
    return Application::classLoaderInDirectory($directory);
}

/**
 * Check if is executed in CLI
 *
 * @return bool
 */
function app_is_cli(): bool
{
    return Application::isCli();
}

/**
 * Get a PSR-11 container
 *
 * @param string $provider
 * 
 * @return mixed
 */
function container(string $provider): mixed
{
    return Application::provider($provider);
}
