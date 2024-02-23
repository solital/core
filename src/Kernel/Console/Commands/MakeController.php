<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Http\Controller\Controller;
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace};

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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $controller_dir = Application::getRootApp('Components/Controller/', Application::DEBUG);

        if (isset($options->remove)) {
            $this->removeComponent($controller_dir, $arguments->controller_name . ".php");
        }

        if (!isset($arguments->controller_name)) {
            $this->error("Error: Controller name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->controller_name);

        $res = $this->createComponent($data, [
            'component_name' => $arguments->controller_name,
            'directory' => $controller_dir
        ]);

        if ($res == true) {
            $this->success("Controller successfully created!")->print()->break();
            return true;
        }

        $this->error("Error: Controller already exists!")->print()->break();
        return false;
    }

    /**
     * @param  mixed $controller_name
     * @return string
     */
    private function codeGenerated(string $controller_name): string
    {
        $home_method = (new Method('home'))
            ->setPublic()
            ->setBody("return view('');")
            ->setReturnType('mixed')
            ->addComment("@return mixed");

        $construct = (new Method('__construct'))
            ->setPublic()
            ->setBody("parent::__construct();")
            ->addComment("Construct");

        $class = (new ClassType($controller_name))
            ->setExtends(Controller::class)
            ->addMember($construct)
            ->addMember($home_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Components\Controller"))
            ->add($class)
            ->addUse(Controller::class);

        return $data;
    }
}
