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
     * @throws WolfException
     */
    protected function generateTemplate(string $view): mixed
    {
        if (!$this->viewExists($view)) {
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
     * @param string $view
     * 
     * @return void
     */
    public function extend(string $view): void
    {
        $all_args = Wolf::getAllArgs();
        extract($all_args, EXTR_SKIP);

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
    private function convertPhpTags(string $view): string
    {
        $render = file_get_contents($view);
        $render = $this->checkInternalFunctions($render);
        $render = str_replace(["{{", "}}"], ["<?=", "?>"], (string) $render);
        $render = str_replace(["{%", "%}"], ["<?php", "?>"], $render);

        return $render;
    }

    /**
     * @param string $view
     * 
     * @return string
     */
    private function generateTempFile(string $view): string
    {
        $render = $this->convertPhpTags($view);
        $basename_view = basename($view, ".php");
        $view_in_temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  $basename_view . ".temp.php";

        if ($this->viewExists($view_in_temp)) {
            unlink($view_in_temp);
        }

        file_put_contents($view_in_temp, $render);
        clearstatcache(true, $view);

        return $view_in_temp;
    }

    private function viewExists(string $file_path): bool
    {
        $file_exists = false;

        //clear cached results
        clearstatcache(true, $file_path);

        //trim path
        $file_dir = trim(dirname($file_path));

        //normalize path separator
        $file_dir = str_replace('/', DIRECTORY_SEPARATOR, $file_dir) . DIRECTORY_SEPARATOR;

        //trim file name
        $file_name = trim(basename($file_path));

        //rebuild path
        $file_path = $file_dir . "{$file_name}";

        //If you simply want to check that some file (not directory) exists, 
        //and concerned about performance, try is_file() instead.
        //It seems like is_file() is almost 2x faster when a file exists 
        //and about the same when it doesn't.

        $file_exists = is_file($file_path);

        //$file_exists=file_exists($file_path);

        return $file_exists;
    }
}
