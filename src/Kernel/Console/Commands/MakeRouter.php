<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Kernel\DebugCore;

class MakeRouter extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:router";

    /**
     * @var array
     */
    protected array $arguments = ["router_name"];

    /**
     * @var string
     */
    protected string $description = "Create a new router";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $router_dir = Application::getRoot('routers/', DebugCore::isCoreDebugEnabled());
        $router_template = Application::getConsoleComponent('RouterName.php');
        $comment = "";

        if (empty($arguments->router_name)) {
            ConsoleOutput::error("Error: You need to define a name for your route")->print()->break();
            return false;
        }

        if (isset($options->comment)) {
            $comment = "- " . $options->comment;
        }

        $this->generateComponent($router_template, $router_dir, $arguments->router_name, [
            'CommentDefault' => $comment,
            'SolitalVersion' => Application::SOLITAL_VERSION
        ]);

        return $this;
    }

    /**
     * generateComponent
     *
     * @param  mixed $component_template
     * @param  mixed $component_dir
     * @param  mixed $argument_name
     * @param  mixed $replace
     * @return bool
     */
    public function generateComponent(
        string $component_template,
        string $component_dir,
        string $argument_name,
        ?array $replace = null
    ): bool {
        $folder = Application::provider('handler-file');
        $output_template = file_get_contents($component_template);

        if (str_contains($output_template, 'NameDefault')) {
            $output_template = str_replace('NameDefault', $argument_name, $output_template);
        }

        if ($replace != null) {
            foreach ($replace as $key => $replace_text) {
                if (str_contains($output_template, $key) && $key != "") {
                    $output_template = str_replace($key, $replace_text, $output_template);
                }
            }
        }

        $file_exists = $component_dir . $argument_name . ".php";

        if (!file_exists($file_exists)) {
            if (!is_dir($component_dir)) {
                $folder->create($component_dir);
            }

            file_put_contents($component_dir . $argument_name . ".php", $output_template);
            ConsoleOutput::success("Router successfully created!")->print()->break();
            return true;
        }

        ConsoleOutput::error("Error: Router already exists!")->print()->break();
        return false;
    }
}
