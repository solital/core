<?php

namespace Solital\Core\Wolf\Functions;

use Psr\Log\LogLevel;
use Solital\Core\Logger\Handler\LogfileHandler;
use Solital\Core\Kernel\Application;
use Solital\Core\Wolf\Exception\WolfException;
use Solital\Core\Wolf\Wolf;

trait ExtendsTrait
{
    /**
     * @param string $view
     */
    protected function generateTemplate(string $view)
    {
        try {
            if (file_exists($view)) {
                ob_start();

                extract($this->getArgs(), EXTR_SKIP);

                $view_in_temp = $this->generateTempFile($view);

                include_once $view_in_temp;

                if ($this->time != null) {
                    $res = ob_get_contents();
                    ob_flush();
                    file_put_contents($this->file_cache, $res);
                }

                return ob_get_clean();
            } else {
                throw new WolfException("Template " . basename($view) . " not found");
            }
        } catch (WolfException $e) {
            $this->logger->addHandler(
                LogLevel::CRITICAL,
                new LogfileHandler('template'),
            );
            $this->logger->critical("Template '" . basename($view) . "' not found");

            Application::exceptionHandler($e, "Template '" . basename($view) . "' not found", 403);
        }
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
     * @param string $view
     * 
     * @return string
     */
    private function generateTempFile(string $view): string
    {
        $render = file_get_contents($view);
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
