<?php

use Solital\Core\Kernel\Application;

/**
 * @param string $view
 * @param array|null $args
 */
function view(string $view, array $args = null)
{
    $wolf = Application::provider('solital-wolf');
    $wolf->setArgs($args);
    $wolf->setView($view);
    echo $wolf->render();
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_css(string $asset): string
{
    return Application::provider('solital-wolf')->css($asset);
}

/**
 * @return string
 */
function load_min_css(): string
{
    return Application::provider('solital-wolf')->css('style.min.css');
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_js(string $asset): string
{
    return Application::provider('solital-wolf')->js($asset);
}

/**
 * @return string
 */
function load_min_js(): string
{
    return Application::provider('solital-wolf')->js('script.min.js');
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_img(string $asset): string
{
    return Application::provider('solital-wolf')->img($asset);
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_file(string $asset): string
{
    return Application::provider('solital-wolf')->file($asset);
}

/**
 * @param string $view
 */
function extend(string $view)
{
    Application::provider('solital-wolf')->extend($view);
}

/**
 * @param string $needle
 * @param bool $value
 * 
 * @return string
 */
function conditional(string $needle, bool $value): string
{
    return ($value == true) ? $needle : "";
}
