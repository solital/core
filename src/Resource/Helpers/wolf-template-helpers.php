<?php

use Solital\Core\Course\Course;
use Solital\Core\Kernel\Application;

/**
 * Render a view
 * 
 * @param string $view
 * @param array|null $args
 * @param bool $escape_special_chars
 */
function view(string $view, ?array $args = null, bool $escape_special_chars = true)
{
    $wolf = Application::provider('solital-wolf');
    $wolf->setArgs($args, $escape_special_chars);
    $wolf->setView($view);
    echo $wolf->render();
}

/**
 * Load a CSS file into the `public/assets/_css/` folder
 * 
 * @param string $asset
 * 
 * @return string
 */
function load_css(string $asset): string
{
    return Application::provider('solital-wolf')->css($asset);
}

/**
 * Loads the minified CSS file created by the `minify()->style()` method
 * 
 * @return string
 */
function load_min_css(): string
{
    return Application::provider('solital-wolf')->css('style.min.css');
}

/**
 * Load a Javascript file into the `public/assets/_js/` folder
 * 
 * @param string $asset
 * 
 * @return string
 */
function load_js(string $asset): string
{
    return Application::provider('solital-wolf')->js($asset);
}

/**
 * Loads the minified Javascript file created by the `minify()->script()` method
 * 
 * @return string
 */
function load_min_js(): string
{
    return Application::provider('solital-wolf')->js('script.min.js');
}

/**
 * Load a image file into the `public/assets/_img/` folder
 * 
 * @param string $asset
 * 
 * @return string
 */
function load_img(string $asset): string
{
    return Application::provider('solital-wolf')->img($asset);
}

/**
 * Load a file into the `public/assets/` folder
 * 
 * @param string $asset
 * 
 * @return string
 */
function load_file(string $asset): string
{
    return Application::provider('solital-wolf')->file($asset);
}

/**
 * Includes any template that is inside the resource/view folder
 * 
 * @param string $view
 */
function extend(string $view)
{
    Application::provider('solital-wolf')->extend($view);
}

/**
 * Displayed code only in production mode or only in development mode
 * 
 * @param string $needle
 * @param bool $value
 * 
 * @return string
 */
function conditional(string $needle, bool $value): string
{
    return ($value == true) ? $needle : "";
}

/**
 * Get current csrf-token
 * 
 * @return string|null
 */
function csrf_token(int $minutes = 1800): ?string
{
    $baseVerifier = Course::router()->getCsrfVerifier();

    return ($baseVerifier !== null) ?
        "<input type='hidden' name='csrf_token' value='" . $baseVerifier->getTokenProvider()->setToken($minutes) . "'>" :
        null;
}

/**
 * Form Method Spoofing
 * 
 * @param string $method
 * @return string
 */
function spoofing(string $method): string
{
    $method = strtoupper($method);
    return "<input type='hidden' name='_method' value='" . $method . "' readonly />";
}
