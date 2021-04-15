<?php

namespace Solital\Core\Wolf;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

trait WolfMinifyTrait
{
    /**
     * @var string
     */
    private static string $dir;

    /**
     * @var string
     */
    private static string $dir_public;

    /**
     * @var bool
     */
    protected static bool $minify_mode = false;

    /**
     * @return self
     */
    public static function minify(bool $mode = true): self
    {
        self::$minify_mode = $mode;

        return new static();
    }

    /**
     * @return bool
     */
    public function style(): bool
    {
        $minCSS = new CSS();
        $cssDir = scandir(self::getDirCss());

        foreach ($cssDir as $cssItem) {
            $cssFile = self::getDirCss() . $cssItem;

            if (is_file($cssFile) && pathinfo($cssFile)["extension"] == "css") {
                $minCSS->add($cssFile);
            }
        }

        $minCSS->minify(self::getPublicDirCss() . 'style.min.css');

        return true;
    }

    /**
     * @return bool
     */
    public function script(): bool
    {
        $minJS = new JS();
        $jsDir = scandir(self::getDirJs());

        foreach ($jsDir as $jsItem) {
            $jsFile = self::getDirJs() . $jsItem;

            if (is_file($jsFile) && pathinfo($jsFile)["extension"] == "js") {
                $minJS->add($jsFile);
            }
        }

        $minJS->minify(self::getPublicDirJs() . 'script.min.js');

        return true;
    }

    /**
     * @return bool
     */
    public function all(): bool
    {
        $this->style();
        $this->script();

        return true;
    }

    /**
     * @return string
     */
    private static function getDirCss(): string
    {
        self::$dir = SITE_ROOT . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_css" . DIRECTORY_SEPARATOR;

        return self::$dir;
    }

    /**
     * @return string
     */
    private static function getDirJs(): string
    {
        self::$dir = SITE_ROOT . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_js" . DIRECTORY_SEPARATOR;

        return self::$dir;
    }

    /**
     * @return string
     */
    private static function getPublicDirCss(): string
    {
        self::$dir_public = SITE_ROOT . DIRECTORY_SEPARATOR . "public"  . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_css" . DIRECTORY_SEPARATOR;

        return self::$dir_public;
    }

    /**
     * @return string
     */
    private static function getPublicDirJs(): string
    {
        self::$dir_public = SITE_ROOT . DIRECTORY_SEPARATOR . "public"  . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "_js" . DIRECTORY_SEPARATOR;

        return self::$dir_public;
    }
}
