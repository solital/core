<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;

class MakeController extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:controller";

    /**
     * @var array
     */
    protected array $arguments = ["controller_name"];

    /**
     * @var string
     */
    protected string $description = "Create a Controller class inside the 'app/Components/Controller' folder";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $controller_dir = Application::getRootApp('Components/Controller/', Application::DEBUG);
        $controller_template = Application::getConsoleComponent('ControllerTemplate.php');

        if (isset($options->remove)) {
            $this->removeComponent($controller_dir, $arguments->controller_name . ".php");
        }

        $res = $this->createComponent($controller_template, $controller_dir, $arguments->controller_name);

        if ($res == true) {
            $this->success("Controller successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Controller already exists!")->print()->break();

            return false;
        }

        return $this;
    }
}
