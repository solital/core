<?php

namespace Solital\Core\Wolf;

use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Logger;
use Solital\Core\Resource\Collection\ArrayCollection;
use Solital\Core\Resource\Str\Str;
use Solital\Core\Wolf\Exception\WolfException;
use Solital\Core\Wolf\{WolfCacheTrait, WolfMinifyTrait};
use Solital\Core\Wolf\Functions\{AssetsTrait, ExtendsTrait};

class Wolf
{
    use WolfCacheTrait, WolfMinifyTrait, AssetsTrait, ExtendsTrait;

    /**
     * @var null|string
     */
    private ?string $main_url;

    /**
     * @var string
     */
    private string $dir_view;

    /**
     * @var string
     */
    private string $view = "";

    /**
     * @var array
     */
    private array $args = [];

    /**
     * @var array
     */
    protected static array $allArgs = [];

    /**
     * Construct
     */
    public function __construct()
    {
        if (Application::isCli() == true) {
            $this->main_url = Application::getRootApp("view/assets/");
            $this->dir_view = Application::getRootApp("view/");
        } else {
            $this->main_url = '//' . $_SERVER['HTTP_HOST'] . "/";
            $this->dir_view = Application::getRoot("/resources/view");
        }
    }

    /**
     * @param array $args
     * 
     * @return void
     */
    public static function setAllArgs(array $args): void
    {
        self::$allArgs = $args;
    }

    /**
     * @return null|array
     */
    public static function getAllArgs(): ?array
    {
        return self::$allArgs;
    }

    /**
     * Display data for your view
     * 
     * @param array|null $args
     * 
     * @return Wolf
     */
    public function setArgs(?array $args, bool $escape_special_chars = true): Wolf
    {
        if (isset($args)) {
            $this->args = $args;
            self::$allArgs = $args;

            if ($escape_special_chars == true) {
                $this->args = $this->htmlspecialcharsRecursive($this->args);
                $this->args = (new ArrayCollection(self::$allArgs))->merge($this->args)->all();
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getArgs(): ?array
    {
        return $this->args;
    }

    /**
     * Set template view
     * 
     * @param string $view
     * 
     * @return Wolf
     */
    public function setView(string $view): Wolf
    {
        if (Str::contains($view, '.php')) $view = str_replace('.php', '', $view);
        if (Str::contains($view, '.') || Str::contains($view, '/')) {
            $view = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $view);
        }

        $this->view = $this->dir_view . $view . '.php';
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        if (!$this->viewExists($this->view)) {
            Logger::channel('single')->error("Template '" . basename($this->view) . "' not found");
            throw new WolfException("Template " . basename($this->view) . " not found");
        }

        $template = $this->convertPhpTags($this->view);

        if (!empty($this->getArgs())) {
            foreach ($this->getArgs() as $key => $value) {
                if (Str::contains($template, '<?= $' . $key . ' ?>')) {
                    $template = str_replace('<?= $' . $key . ' ?>', $value, $template);
                }
            }
        }

        return $template;
    }

    /**
     * @return bool
     */
    private function viewExists(string $view): bool
    {
        return Application::fileExistsWithoutCache($view) ? true : false;
    }

    /**
     * Render template with arguments
     * 
     * @return string
     */
    public function render(): ?string
    {
        $config = Application::yamlParse('bootstrap.yaml');

        if (is_array($config)) {
            $this->setCacheTime($config);
            $this->setMinify($config);
        }

        $cache_template = $this->generateCacheNameFile($this->view);
        $cache_template = $this->loadCache($cache_template);

        if (!empty($cache_template) || $cache_template != null) {
            echo $cache_template;
            die;
        }

        return $this->generateTemplate($this->view);
    }

    /**
     * Render HTML
     *
     * @param string $html
     * 
     * @return string
     * 
     */
    public static function renderHtml(string $html): string
    {
        ob_start();
        echo htmlspecialchars($html);
        return ob_get_clean();
    }

    /**
     * Escape HTML tags
     * 
     * @param mixed $args
     * 
     * @return mixed
     */
    private function htmlspecialcharsRecursive(mixed $args): mixed
    {
        foreach ($this->getAllowIndex() as $key => $value) {
            if (is_array($args)) {
                if (array_key_exists($key, $args)) unset($args[$key]);
            }
        }

        if (is_array($args)) return array_map($this->htmlspecialcharsRecursive(...), $args);
        if (is_scalar($args)) return htmlspecialchars($args, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        return $args;
    }
}
