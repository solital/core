<?php

namespace Solital\Core\Wolf;

use Solital\Core\Kernel\Application;
use Solital\Core\Wolf\Functions\ExtendsTrait;

trait WolfCacheTrait
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
     * Create a cache for a single view
     * 
     * @param array|string $time
     * 
     * @return self
     */
    public function setCacheTime(array|string $time): self
    {
        if (is_array($time)) {
            if ($time['wolf_cache']['enabled'] == true) {
                match ($time['wolf_cache']['time']) {
                    'minute' => $this->forOneMinute(),
                    'hour' => $this->forOneHour(),
                    'day' => $this->forOneDay(),
                    'week' => $this->forOneWeek()
                };
            }

            return $this;
        }

        if (is_string($time)) {
            match ($time) {
                'minute' => $this->forOneMinute(),
                'hour' => $this->forOneHour(),
                'day' => $this->forOneDay(),
                'week' => $this->forOneWeek()
            };

            return $this;
        }

        return $this;
    }

    /**
     * Cache a template for one minute
     * 
     * @return self
     */
    public function forOneMinute(): self
    {
        $this->time = date('Hi');
        return $this;
    }

    /**
     * Cache a template for one hour
     * 
     * @return self
     */
    public function forOneHour(): self
    {
        $this->time = date('H');
        return $this;
    }

    /**
     * Cache a template for one day
     * 
     * @return self
     */
    public function forOneDay(): self
    {
        $this->time = date('N');
        return $this;
    }

    /**
     * Cache a template for one week
     * 
     * @return self
     */
    public function forOneWeek(): self
    {
        $this->time = date('W');
        return $this;
    }

    /**
     * @return string
     */
    protected function getFolderCache(): string
    {
        (Application::isCli() == true) ?
            $this->cache_dir = Application::getRootApp('view/cache/') :
            $this->cache_dir = Application::getRootApp("Storage/cache/wolf/");

        if (!is_dir($this->cache_dir)) Application::provider('handler-file')->create($this->cache_dir);
        return $this->cache_dir;
    }

    /**
     * @param string $view
     * 
     * @return null|string
     */
    protected function generateCacheNameFile(string $view): ?string
    {
        if (!empty($this->time) || $this->time != null) {
            $this->file_cache = $this->getFolderCache() . basename($view, ".php") . "-" . date('Ymd') . "-" . $this->time . ".cache.php";
            return $this->file_cache;
        }

        return null;
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
}
