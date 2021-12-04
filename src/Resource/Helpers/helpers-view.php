<?php

use Solital\Core\Wolf\Wolf;

/**
 * @param string $view
 * @param array|null $data
 * @param string $ext
 */
function view(string $view, array $data = null, string $ext = "php")
{
    return Wolf::loadView($view, $data, $ext);
}

/**
 * @param string $asset
 * 
 * @return string
 */
function loadCss(string $asset): string
{
    $css = Wolf::loadCss($asset);

    return $css;
}

/**
 * @return string
 */
function loadMinCss(): string
{
    $css = Wolf::loadCss('style.min.css');

    return $css;
}

/**
 * @param string $asset
 * 
 * @return string
 */
function loadJs(string $asset): string
{
    $css = Wolf::loadJs($asset);

    return $css;
}

/**
 * @return string
 */
function loadMinJs(): string
{
    $css = Wolf::loadJs('script.min.js');

    return $css;
}

/**
 * @param string $asset
 * 
 * @return string
 */
function loadImg(string $asset): string
{
    $css = Wolf::loadImg($asset);

    return $css;
}

/**
 * @param string $asset
 * 
 * @return string
 */
function loadFile(string $asset): string
{
    $css = Wolf::loadFile($asset);

    return $css;
}
