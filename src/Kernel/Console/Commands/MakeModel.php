<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;

class MakeModel extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:model";

    /**
     * @var array
     */
    protected array $arguments = ["model_name"];

    /**
     * @var string
     */
    protected string $description = "Create a Model class inside the 'app/Components/Model' folder";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $model_dir = Application::getRootApp('Components/Model/', Application::DEBUG);
        $model_template = Application::getConsoleComponent('ModelTemplate.php');

        if (isset($options->remove)) {
            $this->removeComponent($model_dir, $arguments->model_name . ".php");
        }

        $res = $this->createComponent($model_template, $model_dir, $arguments->model_name);

        if ($res == true) {
            $this->success("Model successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Model already exists!")->print()->break();

            return false;
        }

        return $this;
    }
}
