<?php

namespace Solital\Core\Wolf;

use MatthiasMullie\Minify\{CSS, JS};
use Solital\Core\Kernel\Application;

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
     * @param mixed $type
     * 
     * @return self
     */
    public function setMinify(mixed $type): self
    {
        if (is_array($type)) {
            if ($type['wolf_minify'] != false) {
                if ($type['wolf_minify'] == 'style') {
                    $this->style();
                } elseif ($type['wolf_minify'] == 'script') {
                    $this->script();
                } elseif ($type['wolf_minify'] == 'all') {
                    $this->all();
                }
            }
        } elseif (is_string($type)) {
            if ($type == 'style') {
                $this->style();
            } elseif ($type == 'script') {
                $this->script();
            } elseif ($type == 'all') {
                $this->all();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function style(): bool
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
    protected function script(): bool
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
    protected function all(): bool
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
        self::$dir = Application::getRoot("resources/assets/_css/");

        return self::$dir;
    }

    /**
     * @return string
     */
    private static function getDirJs(): string
    {
        self::$dir = Application::getRoot("resources/assets/_js/");

        return self::$dir;
    }

    /**
     * @return string
     */
    private static function getPublicDirCss(): string
    {
        self::$dir_public = Application::getRoot("public/assets/_css/");

        return self::$dir_public;
    }

    /**
     * @return string
     */
    private static function getPublicDirJs(): string
    {
        self::$dir_public = Application::getRoot("public/assets/_js/");

        return self::$dir_public;
    }
}
