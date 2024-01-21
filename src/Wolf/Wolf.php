<?php

namespace Solital\Core\Wolf;

use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Logger;
use Solital\Core\Resource\Collection\ArrayCollection;
use Solital\Core\Wolf\{WolfCacheTrait, WolfMinifyTrait};
use Solital\Core\Wolf\Exception\WolfException;
use Solital\Core\Wolf\Functions\{AssetsTrait, ExtendsTrait};

class Wolf
{
    use WolfCacheTrait;
    use WolfMinifyTrait;
    use AssetsTrait;
    use ExtendsTrait;

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
     * @var mixed
     */
    private static mixed $original_args;

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
     */
    public static function setAllArgs(array $args)
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
     * @param array|null $args
     * 
     * @return Wolf
     */
    public function setArgs(?array $args): Wolf
    {
        if (isset($args)) {
            $this->args = $args;
            self::$allArgs = $args;
            $this->args = $this->htmlspecialcharsRecursive($this->args);
            $this->args = (new ArrayCollection(self::$allArgs))->merge($this->args)->all();
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
     * @param string $view
     * 
     * @return Wolf
     */
    public function setView(string $view): Wolf
    {
        $view = str_replace(".", DIRECTORY_SEPARATOR, $view);

        if (str_contains('.', $view)) {
            $view = str_replace(".", DIRECTORY_SEPARATOR, $view);
        }

        $this->view = $this->dir_view . $view . '.php';

        return $this;
    }

    /**
     * @return string
     */
    public function render(): ?string
    {
        $config = Application::getYamlVariables(5, 'bootstrap.yaml');
        
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
     * @param mixed $args
     * 
     * @return mixed
     */
    private function htmlspecialcharsRecursive(mixed $args): mixed
    {
        foreach ($this->getAllowIndex() as $key => $value) {
            if (is_array($args)) {
                if (array_key_exists($key, $args)) {
                    unset($args[$key]);
                }
            }
        }

        if (is_array($args)) {
            return array_map(array($this, 'htmlspecialcharsRecursive'), $args);
        } else if (is_scalar($args)) {
            return htmlspecialchars($args, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        } else {
            return $args;
        }
    }
}
