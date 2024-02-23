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
    protected string $command = "storage:clear";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Clear the Solital storage files";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        if (isset($options->session)) {
            return $this->clearSession();
        }

        if (isset($options->cache)) {
            return $this->clearCache();
        }

        if (isset($options->schedules)) {
            return $this->clearSchedule();
        }

        if (isset($options->log)) {
            return $this->clearLogs();
        }

        $this->clearSession();
        $this->clearCache();
        $this->clearSchedule();
        $this->clearLogs();

        return $this;
    }

    /**
     * @return bool
     */
    public function clearSession(): bool
    {
        if (Application::DEBUG == true) {
            $this->error("SESSION: Debug mode enabled! It is not possible to delete the sessions!")->print()->break();
            return false;
        }

        $dir = Application::getRootApp('Storage/session/', false);
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

        foreach ($dir_cache as $folder) {
            $dir = Application::getRootApp('Storage/cache/' . $folder, false);
            $this->eraseFiles($dir);
        }

        $this->success("Cache was cleared successfully!")->print()->break();

        return true;
    }

    /**
     * @return bool
     */
    public function clearSchedule(): bool
    {
        $dir = Application::getRootApp('Storage/schedules/', false);
        $this->eraseFiles($dir);
        $this->success("Log schedules was cleared successfully!")->print()->break();

        return true;
    }

    /**
     * @return bool
     */
    public function clearLogs(): bool
    {
        $dir = Application::getRootApp('Storage/log/', false);
        $this->eraseFiles($dir);
        $this->success("Logs was cleared successfully!")->print()->break();

        return true;
    }

    /**
     * @param string $dir
     * 
     * @return bool
     */
    private function eraseFiles(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $directory = dir($dir);

        while ($file = $directory->read()) {
            if (($file != '.') && ($file != '..')) {
                unlink($dir . $file);
            }
        }

        $directory->close();

        return true;
    }
}
