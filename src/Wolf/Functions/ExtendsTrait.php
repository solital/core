<?php

namespace Solital\Core\Wolf\Functions;

use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Logger;
use Solital\Core\Resource\Str\Str;
use Solital\Core\Security\Hash;
use Solital\Core\Wolf\Exception\WolfException;
use Solital\Core\Wolf\Wolf;

trait ExtendsTrait
{
    use AssetsTrait;

    /**
     * Generate a template
     * 
     * @param string $view
     * 
     * @return mixed
     * @throws WolfException
     */
    protected function generateTemplate(string $view): mixed
    {
        if (!Application::fileExistsWithoutCache($view)) {
            Logger::channel('single')->error("Template '" . basename($view) . "' not found");
            throw new WolfException("Template " . basename($view) . " not found");
        }

        ob_start();

        $args = $this->getArgs();
        extract($args, EXTR_SKIP);

        $view_in_temp = $this->generateTempFile($view);
        include_once $view_in_temp;

        if ($this->time != null) {
            $result = ob_get_contents();
            ob_flush();
            file_put_contents($this->file_cache, $result);
        }

        clearstatcache(true, $view);
        return ob_get_clean();
    }

    /**
     * Extends a template
     * 
     * @param string $view
     * 
     * @return void
     */
    public function extend(string $view): void
    {
        $all_args = Wolf::getAllArgs();
        extract($all_args, EXTR_SKIP);

        if (Str::endsWith($view, ".php")) {
            $view = str_replace(".php", "", $view);
        }

        $view = str_replace(["/", "."], DIRECTORY_SEPARATOR, $view);
        $view = $this->dir_view . $view . ".php";

        $view_in_temp = $this->generateTempFile($view);
        include_once $view_in_temp;
    }

    /**
     * Replace all internal function on PHP code
     *
     * @param string $view
     * 
     * @return mixed
     */
    private function checkInternalFunctions(string $view): mixed
    {
        $view = str_replace("{% production %}", "<?php if (self::production() == true) : ?>", $view);
        $view = str_replace("{% endproduction %}", "<?php endif; ?>", $view);
        $view = str_replace("{% development %}", "<?php if (self::production() == false) : ?>", $view);
        $view = str_replace("{% enddevelopment %}", "<?php endif; ?>", $view);
        $view = str_replace("generate_link", "self::generate_link", $view);
        return $view;
    }

    /**
     * Execute code in production or development mode
     *
     * @return mixed
     */
    public function production(): mixed
    {
        $config = $this->getConfigYaml();
        return $config['production_mode'];
    }

    /**
     * @param string $email
     * @param string $time
     * 
     * @return string
     */
    public function generate_link(string $email, string $uri, string $time): string
    {
        $hash = Hash::encrypt($email, $time);
        $link = $uri . $hash;
        return $link;
    }

    /**
     * Convert Wolf tags to PHP tags for temporary template
     * 
     * @param string $view
     * 
     * @return string
     */
    private function convertPhpTags(string $view): string
    {
        $render = file_get_contents($view);
        $render = $this->checkInternalFunctions($render);
        $render = str_replace(["{{", "}}"], ["<?=", "?>"], (string) $render);
        $render = str_replace(["{%", "%}"], ["<?php", "?>"], $render);
        return $render;
    }

    /**
     * Generate a temporary template
     * 
     * @param string $view
     * 
     * @return string
     */
    private function generateTempFile(string $view): string
    {
        $render = $this->convertPhpTags($view);
        $basename_view = basename($view, ".php");
        $view_in_temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  $basename_view . ".temp.php";

        if (Application::fileExistsWithoutCache($view_in_temp)) {
            unlink($view_in_temp);
        }

        file_put_contents($view_in_temp, $render);
        return $view_in_temp;
    }
}
