<?php

namespace Solital\Core\Kernel\Console;

use Solital\Core\Kernel\Application;
use Solital\Core\Console\InputOutput;
use Solital\Core\FileSystem\HandleFiles;

trait HelpersTrait
{
    /**
     * @param string $component_name
     * @param array $config
     * 
     * @return bool
     */
    public function createComponent(string $data, array $config): bool
    {
        $folder = new HandleFiles();

        $file_exists = $config['directory'] . $config['component_name'] . ".php";

        if (!file_exists($file_exists)) {
            if (!is_dir($config['directory'])) {
                $folder->create($config['directory']);
            }

            file_put_contents($config['directory'] . $config['component_name'] . '.php', "<?php \n\n" . $data);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $dir
     * @param string $component
     * 
     * @return void
     */
    public function removeComponent(string $dir, string $component): void
    {
        $input_output = new InputOutput();
        $handle_files = new HandleFiles();

        $input_output->confirmDialog("Are you sure you want to delete this components? (this process cannot be undone)? ", "Y", "N", false);

        $input_output->confirm(function () use ($dir, $component, $handle_files) {
            $handle_files->folder($dir)->fileExists($component, true);

            $this->success("Component successfully removed!")->print()->break();
        });

        $input_output->refuse(function () {
            $this->line("Abort!")->print()->break()->exit();
        });
    }

    /**
     * @param string $auth_controller_dir
     * @param string $auth_template_dir
     * @param string $file_name
     * 
     * @return bool
     */
    public function createAuthComponents(
        string $auth_controller_dir,
        string $auth_template_dir,
        string $file_name
    ): bool {
        $handle_files = new HandleFiles();
        $res = $handle_files->folder($auth_controller_dir)->fileExists($file_name);

        if ($res != true) {
            $handle_files->create($auth_controller_dir);
            $res = $handle_files->getAndPutContents($auth_template_dir, $auth_controller_dir . $file_name);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $components
     * 
     * @return void
     */
    public function removeAuthComponent(array $components): void
    {
        $exists = [];

        foreach ($components as $file) {
            if (!file_exists($file)) {
                $exists[] = "";
            } else {
                $exists[] = $file;
            }
        }

        if (empty($exists)) {
            $this->success("No component found")->print()->break()->exit();
        } else {
            $input_output = new InputOutput();
            $input_output->confirmDialog("Are you sure you want to delete this components? (this process cannot be undone)? ", "Y", "N", false);
            $input_output->confirm(function () use ($components) {
                foreach ($components as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    } else {
                        return false;
                    }
                }

                $this->success("Components successfully removed!")->print()->break();
            });

            $input_output->refuse(function () {
                $this->line("Abort!")->print()->break()->exit();
            });
        }
    }
}
