<?php

namespace Solital\Core\Wolf;

use Solital\Core\Wolf\WolfCache;
use Solital\Core\Exceptions\NotFoundException;

class Wolf extends WolfCache
{
    /**
     * @var string
     */
    private static $main_url;

    /**
     * @return string
     */
    private static function getInstance(): string
    {
        return self::$main_url = '//' . $_SERVER['HTTP_HOST'] . "/";
    }

    /**
     * @param string $view
     * @param array|null $data
     * @param string $ext
     * 
     * @return Wolf
     */
    public static function loadView(string $view, array $data = null, string $ext = "php")
    {
        $view = str_replace(".", DIRECTORY_SEPARATOR, $view);
        $file = ROOT . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $view . '.' . $ext;

        self::$file_cache = self::$cache_dir . $view . "-" . date('Ymd') . "-" . self::$time . ".cache.php";

        if (strpos($view, "/")) {
            $file = ROOT . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $view . '.' . $ext;

            $viewForCache = str_replace("/", ".", $view);
            self::$file_cache = self::$cache_dir . $viewForCache . "-" . date('Ymd') . "-" . self::$time . ".cache.php";
        }

        if (isset($data)) {
            extract($data, EXTR_SKIP);
        }

        if (file_exists(self::$file_cache)) {
            include_once self::$file_cache;
            die;
        }

        if (file_exists($file)) {
            if (self::$time != null) {
                ob_start();
            }

            include $file;

            if (self::$time != null) {
                $res = ob_get_contents();
                ob_flush();
                file_put_contents(self::$file_cache, $res);
            }
        } else {
            NotFoundException::WolfNotFound($view, $ext);
        }

        return __CLASS__;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadFile(string $asset): string
    {
        $file = self::getInstance() . $asset;

        return $file;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadCss(string $asset): string
    {
        $css = self::getInstance() . 'assets/_css/' . $asset;

        return $css;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadJs(string $asset): string
    {
        $js = self::getInstance() . 'assets/_js/' . $asset;

        return $js;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadImg(string $asset): string
    {
        $img = self::getInstance() . 'assets/_img/' . $asset;

        return $img;
    }
}
