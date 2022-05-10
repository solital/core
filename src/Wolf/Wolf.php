<?php

namespace Solital\Core\Wolf;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Logger\Logger;
use Solital\Core\Kernel\Application;
use Solital\Core\Wolf\{WolfCache, WolfMinifyTrait};
use Solital\Core\Wolf\Functions\{AssetsTrait, ExtendsTrait};

class Wolf extends WolfCache
{
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
    private string $view;

    /**
     * @var array
     */
    private array $args = [];

    /**
     * @var array
     */
    protected static array $allArgs = [];

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Construct
     */
    public function __construct()
    {
        if (Application::isCli() == true) {
            $this->main_url = Application::getRootTest("view/assets/");
            $this->dir_view = Application::getRootTest("view/");
        } else {
            $this->main_url = '//' . $_SERVER['HTTP_HOST'] . "/";
            $this->dir_view = Application::getRoot("/resources/view");
        }

        $this->logger = new Logger('wolf');
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
     * @param string $view
     * @param array|null $args
     * 
     * @return mixed
     */
    public function loadView(string $view, array $args = null): mixed
    {
        $view = str_replace(".", DIRECTORY_SEPARATOR, $view);

        $config = Yaml::parseFile(Application::getDirConfigFiles(5) . '/bootstrap.yaml');

        $this->setArgs($args);
        $this->setView($view);

        if ($config['wolf_cache']['enabled'] == true) {
            switch ($config['wolf_cache']['time']) {
                case 'minute':
                    $this->forOneMinute();
                    break;

                case 'hour':
                    $this->forOneHour();
                    break;

                case 'day':
                    $this->forOneDay();
                    break;

                case 'week':
                    $this->forOneWeek();
                    break;
            }
        }

        return $this->render();
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
     * @param array|null $data
     * 
     * @return Wolf
     */
    public function setView(string $view): Wolf
    {
        if (str_contains('.', $view)) {
            $view = str_replace(".", DIRECTORY_SEPARATOR, $view);
        }

        $this->view = $this->dir_view . $view . '.php';

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $cache_template = $this->generateCache($this->view);
        $cache_template = $this->loadCache($cache_template);

        if (!empty($cache_template) || $cache_template != null) {
            echo $cache_template;
            die;
        }

        return $this->generateTemplate($this->view);
    }

    /**
     * @param mixed $input
     * 
     * @return mixed
     */
    private function htmlspecialcharsRecursive(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map(array($this, 'htmlspecialcharsRecursive'), $input);
        } else if (is_scalar($input)) {
            return htmlspecialchars($input, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        } else {
            return $input;
        }
    }
}
