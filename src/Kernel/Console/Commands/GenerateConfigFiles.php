<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Console\Interface\CommandInterface;

class GenerateConfigFiles extends Command implements CommandInterface
{
    /**
     * @var string
     */
    protected string $command = "generate:files";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Imports Solital Framework's default configuration files";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $config_app_dir = Application::getRootApp('config/', Application::DEBUG);
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config';

        $handle_files = new HandleFiles();
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($config_app_dir);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $config_app_dir . $file_name);
        }

        $this->queueFiles();
        $this->commandFiles();
        $this->success('Configuration files copied successfully!')->print()->break();

        return true;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function queueFiles(): GenerateConfigFiles
    {
        $dir_queue = Application::getRootApp('Queue/', Application::DEBUG);
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . "Queue";

        $handle_files = new HandleFiles();
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($dir_queue);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $dir_queue . $file_name);
        }

        return $this;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function commandFiles(): GenerateConfigFiles
    {
        $dir_queue = Application::getRootApp('Console/', Application::DEBUG);
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . "Commands";

        $handle_files = new HandleFiles();
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($dir_queue);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $dir_queue . $file_name);
        }

        return $this;
    }
}
