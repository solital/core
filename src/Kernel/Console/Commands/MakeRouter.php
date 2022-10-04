<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;

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
    public function handle(object $arguments, object $options): mixed
    {
        $router_dir = Application::getRootApp('routers/', Application::DEBUG);
        $router_template = Application::getConsoleComponent('RouterName.php');
        $comment = null;

        if (empty($arguments->router_name)) {
            $this->error("Error: You need to define a name for your route")->print()->break();

            return false;
        }

        if (isset($options->comment)) {
            $comment = "- " . $options->comment;
        }

        $res = $this->createComponent($router_template, $router_dir, $arguments->router_name, [
            'CommentDefault' => $comment,
            'SolitalVersion' => Application::SOLITAL_VERSION
        ]);

        if ($res == true) {
            $this->success("Router successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Router already exists!")->print()->break();

            return false;
        }

        return $this;
    }
}
