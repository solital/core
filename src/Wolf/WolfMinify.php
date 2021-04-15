<?php

namespace Solital\Core\Wolf;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

class WolfMinify
{
    /**
     * @var string
     */
    private static string $dir;

    /**
     * @return bool
     */
    public static function minifyCss(): bool
    {
        $minCSS = new CSS();
        $cssDir = scandir(self::getDirCss());

        foreach ($cssDir as $cssItem) {
            $cssFile = self::getDirCss() . $cssItem;

            if (is_file($cssFile) && pathinfo($cssFile)["extension"] == "css") {
                $minCSS->add($cssFile);
            }
        }

        $minCSS->minify(self::getDirCss() . 'style.min.css');

        return true;
    }

    /**
     * @return bool
     */
    public static function minifyJs(): bool
    {
        $minJS = new JS();
        $jsDir = scandir(self::getDirJs());

        foreach ($jsDir as $jsItem) {
            $jsFile = self::getDirJs() . $jsItem;

            if (is_file($jsFile) && pathinfo($jsFile)["extension"] == "js") {
                $minJS->add($jsFile);
            }
        }

        $minJS->minify(self::getDirJs() . 'script.min.js');

        return true;
    }

    /**
     * @return bool
     */
    public static function minifyAll(): bool
    {
        self::minifyCss();
        self::minifyJs();

        return true;
    }

    /**
     * @return string
     */
    private static function getDirCss(): string
    {
        self::$dir = SITE_ROOT . DIRECTORY_SEPARATOR . "resources"  . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_css" . DIRECTORY_SEPARATOR;

        return self::$dir;
    }

    /**
     * @return string
     */
    private static function getDirJs(): string
    {
        self::$dir = SITE_ROOT . DIRECTORY_SEPARATOR . "resources"  . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_js" . DIRECTORY_SEPARATOR;

        return self::$dir;
    }
}
