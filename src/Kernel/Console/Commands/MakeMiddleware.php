<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Console\InputOutput;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Console\Interface\CommandInterface;

class MakeMiddleware extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:middleware";

    /**
     * @var array
     */
    protected array $arguments = ["middleware_name"];

    /**
     * @var string
     */
    protected string $description = "Create a Middleware class";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $component_dir = Application::getRootApp('Middleware/', Application::DEBUG);
        $component_template = Application::getConsoleComponent('MiddlewareTemplate.php');

        if (isset($options->remove)) {
            $this->remove($component_dir, $arguments->middleware_name);
        } else {
            $this->create($component_dir, $component_template, $arguments->middleware_name);
        }

        return $this;
    }

    /**
     * @param string $component_dir
     * @param string $component_template
     * @param string $arguments
     * 
     * @return bool
     */
    private function create(string $component_dir, string $component_template, string $arguments): bool
    {
        $output_template = file_get_contents($component_template);

        if (str_contains($output_template, 'NameDefault')) {
            $output_template = str_replace('NameDefault', $arguments, $output_template);
        }

        $file_exists = $component_dir . $arguments . ".php";

        if (!file_exists($file_exists)) {
            file_put_contents($component_dir . $arguments . ".php", $output_template);
            $this->success("Middleware successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Middleware already exists!")->print()->break();

            return false;
        }
    }

    /**
     * @param string $dir
     * @param string $component
     * 
     * @return void
     */
    private function remove(string $dir, string $component): void
    {
        $input_output = new InputOutput();
        $handle_files = new HandleFiles();

        $input_output->confirmDialog("Are you sure you want to delete this components? (this process cannot be undone)? ", "Y", "N", false);

        $input_output->confirm(function () use ($dir, $component, $handle_files) {
            $handle_files->folder($dir)->fileExists($component . ".php", true);

            $this->success("Middleware successfully removed!")->print()->break();
        });

        $input_output->refuse(function () {
            $this->line("Abort!")->print()->break()->exit();
        });
    }
}
