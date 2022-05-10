<?php

use Solital\Core\Wolf\Wolf;

/**
 * @param string $view
 * @param array|null $data
 */
function view(string $view, array $data = null)
{
    echo (new Wolf)->loadView($view, $data);
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_css(string $asset): string
{
    return (new Wolf)->css($asset);
}

/**
 * @return string
 */
function load_min_css(): string
{
    return (new Wolf)->css('style.min.css');
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_js(string $asset): string
{
    return (new Wolf)->js($asset);
}

/**
 * @return string
 */
function load_min_js(): string
{
    return (new Wolf)->js('script.min.js');
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_img(string $asset): string
{
    return (new Wolf)->img($asset);
}

/**
 * @param string $asset
 * 
 * @return string
 */
function load_file(string $asset): string
{
    return (new Wolf)->file($asset);
}

/**
 * @param string $view
 */
function extend(string $view)
{
    (new Wolf)->extend($view);
}