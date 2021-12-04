<?php

namespace Solital\Core\Wolf;

use ModernPHPException\ModernPHPException;
use Solital\Core\Wolf\WolfCache;
use Solital\Core\Wolf\WolfMinifyTrait;

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
        self::$dir_view = SITE_ROOT . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR;

        return self::$dir_view;
    }

    /**
     * @param string $view
     * @param array|null $data
     * @param string $ext
     * 
     * @return bool
     */
    public static function loadView(string $view, array $data = null, string $ext = "php"): bool
    {
        $template = self::generateTemplate($view, $data, $ext);
        echo $template;

        return true;
    }

    /**
     * @param string $view
     * @param array|null $data
     * @param string $ext
     */
    private  static function generateTemplate(string $view, array $data = null, string $ext = "php")
    {
        $view = str_replace(".", DIRECTORY_SEPARATOR, $view);
        $file = self::getDirView() . $view . '.' . $ext;

        /** Create or browse the cached file  */
        self::$file_cache = self::getFolderCache() . $view . "-" . date('Ymd') . "-" . self::$time . ".cache.php";

        if (strpos($view, "/")) {
            $file = self::getDirView() . $view . '.' . $ext;

            $viewForCache = str_replace("/", ".", $view);
            self::$file_cache = self::getFolderCache() . $viewForCache . "-" . date('Ymd') . "-" . self::$time . ".cache.php";
        }

        /** Convert array indexes to variables  */
        if (isset($data)) {
            $data = array_map("htmlspecialchars", $data);
            extract($data, EXTR_SKIP);
        }

        /** Checks whether the cached file exists  */
        if (file_exists(self::$file_cache)) {
            include_once self::$file_cache;
            die;
        }

        try {
            if (file_exists($file)) {
                ob_start();

                include_once $file;

                if (self::$time != null) {
                    $res = ob_get_contents();
                    ob_flush();
                    file_put_contents(self::$file_cache, $res);

                    return ob_get_clean();
                } else {
                    return ob_get_clean();
                }
            } else {
                throw new \Exception("Template '$view.$ext' not found");
            }
        } catch (\Exception $e) {
            if (!empty($_ENV['PRODUCTION_MODE'])) {
                if ($_ENV['PRODUCTION_MODE'] == "true") {
                    (new ModernPHPException())->productionMode();
                } else {
                    (new ModernPHPException())->start()->errorHandler(403, "Template '$view.$ext' not found", $e->getFile(), $e->getLine());
                }
            } else {
                (new ModernPHPException())->start()->errorHandler(403, "Template '$view.$ext' not found", $e->getFile(), $e->getLine());
            }
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
