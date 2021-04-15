<?php

namespace Solital\Core\Wolf;

use Solital\Core\Wolf\WolfCache;
use Solital\Core\Wolf\WolfMinifyTrait;
use Solital\Core\Exceptions\NotFoundException;

class Wolf extends WolfCache
{
    use WolfMinifyTrait;

    /**
     * @var string
     */
    private static string $main_url;

    /**
     * @var string
     */
    private static string $dir_view;

    /**
     * @return string
     */
    private static function getInstance(): string
    {
        return self::$main_url = '//' . $_SERVER['HTTP_HOST'] . "/";
    }

    /**
     * @return string
     */
    private static function getDirView(): string
    {
        self::$dir_view = SITE_ROOT . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR;

        return self::$dir_view;
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

        $file = self::getDirView() . "view" . DIRECTORY_SEPARATOR . $view . '.' . $ext;

        /** Create or browse the cached file  */
        self::$file_cache = self::getFolderCache() . $view . "-" . date('Ymd') . "-" . self::$time . ".cache.php";

        if (strpos($view, "/")) {
            $file = self::getDirView() . $view . '.' . $ext;

            $viewForCache = str_replace("/", ".", $view);
            self::$file_cache = self::getFolderCache() . $viewForCache . "-" . date('Ymd') . "-" . self::$time . ".cache.php";
        }

        /** Convert array indexes to variables  */
        if (isset($data)) {
            extract($data, EXTR_SKIP);
        }

        /** Checks whether the cached file exists  */
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
            NotFoundException::notFound(403, "Template '$view.$ext' not found", "Check if the informed 
            template is in the 'resources/view' folder or if the file extension corresponds to 
            the informed in the 'loadView()' method. ", "Wolf");
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
        $file = self::getInstance() . 'assets/_css/' . $asset;

        return $file;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadJs(string $asset): string
    {
        $file = self::getInstance() . 'assets/_js/' . $asset;

        return $file;
    }

    /**
     * @param string $asset
     * 
     * @return string
     */
    public static function loadImg(string $asset): string
    {
        $file = self::getInstance() . 'assets/_img/' . $asset;

        return $file;
    }
}
