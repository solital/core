<?php

namespace Solital\Core\Wolf;

use Solital\Core\Kernel\Application;
use Solital\Core\Wolf\Functions\ExtendsTrait;

class WolfCache
{
    use ExtendsTrait;

    /**
     * @var string
     */
    protected string $file_cache = "";

    /**
     * @var string
     */
    protected string $cache_dir;

    /**
     * @var null|string
     */
    protected ?string $time = null;

    /**
     * @return self
     */
    public static function cache(): self
    {
        return new static;
    }

    /**
     * @return string
     */
    protected function getFolderCache(): string
    {
        if (Application::isCli() == true) {
            $this->cache_dir = Application::getRootTest('view/cache/');
        } else {
            $this->cache_dir = Application::getRootApp("Storage/cache/wolf/");
        }

        if (!is_dir($this->cache_dir)) {
            \mkdir($this->cache_dir);
        }

        return $this->cache_dir;
    }

    /**
     * @param string $view
     * 
     * @return null|string
     */
    protected function generateCache(string $view): ?string
    {
        if (!empty($this->time) || $this->time != null) {
            $this->file_cache = $this->getFolderCache() . basename($view, ".php") . "-" . date('Ymd') . "-" . $this->time . ".cache.php";
            return $this->file_cache;
        }

        return null;
    }

    /**
     * @param string $view
     * 
     * @return bool
     */
    public function makeCache(string $view): bool
    {
        $dir_view = Application::getRoot("/resources/view");
        $cache_template = $this->generateCache($view);

        if (file_exists($cache_template)) {
            include_once $cache_template;
            exit;
        }

        $cache_template = file_get_contents($dir_view . $view . ".php");
        $cache_template = str_replace(["{{", "}}"], ["<?=", "?>"], $cache_template);
        $cache_template = str_replace(["{%", "%}"], ["<?php", "?>"], $cache_template);

        file_put_contents($this->file_cache, $cache_template);

        return true;
    }

    /**
     * @param string|null $view
     * 
     * @return string|null
     */
    protected function loadCache(?string $view): ?string
    {
        if ($view != null || !empty($view)) {
            if (file_exists($view)) {
                ob_start();
                include_once $view;
                return ob_get_clean();
            }
        }

        return null;
    }

    /**
     * @return WolfCache
     */
    public function forOneMinute(): WolfCache
    {
        $this->time = date('Hi');

        return $this;
    }

    /**
     * @return WolfCache
     */
    public function forOneHour(): WolfCache
    {
        $this->time = date('H');

        return $this;
    }

    /**
     * @return WolfCache
     */
    public function forOneDay(): WolfCache
    {
        $this->time = date('N');

        return $this;
    }

    /**
     * @return WolfCache
     */
    public function forOneWeek(): WolfCache
    {
        $this->time = date('W');

        return $this;
    }
}
