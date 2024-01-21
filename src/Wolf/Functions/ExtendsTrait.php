<?php

namespace Solital\Core\Wolf\Functions;

use Solital\Core\Logger\Logger;
use Solital\Core\Security\Hash;
use Solital\Core\Wolf\Exception\WolfException;
use Solital\Core\Wolf\Wolf;

trait ExtendsTrait
{
    use AssetsTrait;

    /**
     * @param string $view
     * 
     * @return mixed
     */
    protected function generateTemplate(string $view): mixed
    {
        if (file_exists($view)) {
            ob_start();
            extract($this->getArgs(), EXTR_SKIP);

            $view_in_temp = $this->generateTempFile($view);
            include_once $view_in_temp;

            if ($this->time != null) {
                $result = ob_get_contents();
                ob_flush();
                file_put_contents($this->file_cache, $result);
            }

            return ob_get_clean();
        }

        Logger::channel('single')->error("Template '" . basename($view) . "' not found");
        throw new WolfException("Template " . basename($view) . " not found");
    }

    /**
     * @param string $view
     */
    public function extend(string $view)
    {
        extract(Wolf::getAllArgs(), EXTR_SKIP);

        if (str_ends_with($view, ".php")) {
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
     * @param string $view
     * 
     * @return string
     */
    private function generateTempFile(string $view): string
    {
        $render = file_get_contents($view);
        $render = $this->checkInternalFunctions($render);
        $render = str_replace(["{{", "}}"], ["<?=", "?>"], $render);
        $render = str_replace(["{%", "%}"], ["<?php", "?>"], $render);

        $basename_view = basename($view, ".php");
        $view_in_temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  $basename_view . ".temp.php";

        if (file_exists($view_in_temp)) {
            unlink($view_in_temp);
        }

        file_put_contents($view_in_temp, $render);

        return $view_in_temp;
    }
}
