<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Console\Interface\CommandInterface;

class HandleCache extends Command implements CommandInterface
{
    /**
     * @var string
     */
    protected string $command = "cache:clear";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Clear the Solital cache";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        if (isset($options->session)) {
            $this->clearSession();
            exit;
        } elseif (isset($options->cache)) {
            $this->clearCache();
            exit;
        }

        $this->clearSession();
        $this->clearCache();

        return $this;
    }

    /**
     * @return bool
     */
    public function clearSession(): bool
    {
        if (Application::DEBUG == true) {
            $this->error("SESSION: Debug mode enabled! It is not possible to delete the sessions!")->print()->break()->exit();
        }

        $dir = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "session" . DIRECTORY_SEPARATOR;
        $this->eraseFiles($dir);
        $this->success("Sessions was cleared successfully!")->print()->break();

        return true;
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        $dir_cache = [
            'sql',
            'wolf'
        ];

        if (Application::DEBUG == true) {
            $this->error("SESSION: Debug mode enabled! It is not possible to delete the cache!")->print()->break()->exit();
        }

        foreach ($dir_cache as $folder) {
            $dir = constant('SITE_ROOT') . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
            $this->eraseFiles($dir);
        }

        $this->success("Cache was cleared successfully!")->print()->break();

        return true;
    }

    /**
     * @param string $dir
     * 
     * @return void
     */
    private function eraseFiles(string $dir): void
    {
        if (!is_dir($dir)) {
            \mkdir($dir);
        }

        $directory = dir($dir);

        while ($file = $directory->read()) {
            if (($file != '.') && ($file != '..')) {
                unlink($dir . $file);
            }
        }

        $directory->close();
    }
}
