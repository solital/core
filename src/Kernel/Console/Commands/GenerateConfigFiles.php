<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\{
    Interface\CommandInterface,
    Command
};
use Solital\Core\Kernel\Application;
use Solital\Core\FileSystem\HandleFiles;

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

        $this->copyFiles($config_core_dir, $config_app_dir);
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

        $this->copyFiles($config_core_dir, $dir_queue);

        return $this;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function commandFiles(): GenerateConfigFiles
    {
        $dir_queue = Application::getRootApp('Console/', Application::DEBUG);
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . "Commands";

        $this->copyFiles($config_core_dir, $dir_queue);

        return $this;
    }

    /**
     * @param string $config_core_dir
     * @param string $template_dir
     * 
     * @return void
     */
    private function copyFiles(string $config_core_dir, string $template_dir): void
    {
        $handle_files = new HandleFiles();
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($template_dir);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $template_dir . $file_name);
        }
    }
}
