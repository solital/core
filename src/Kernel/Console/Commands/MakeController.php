<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Kernel\{Application, DebugCore};
use Solital\Core\Http\Controller\{Controller, ResourceControllerInterface};
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
    protected string $description = "Create a Controller class";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $controller_dir = Application::getRootApp('Components/Controller/', DebugCore::isCoreDebugEnabled());

        if (isset($options->remove)) {
            $this->removeComponent($controller_dir, $arguments->controller_name . ".php");
        }

        if (!isset($arguments->controller_name)) {
            ConsoleOutput::error("Error: Controller name not found")->print()->break();
            return false;
        }

        if (isset($options->resource)) {
            $data = $this->codeResourceGenerated($arguments->controller_name);
        } else {
            $data = $this->codeGenerated($arguments->controller_name);
        }

        $res = $this->createComponent($data, [
            'component_name' => $arguments->controller_name,
            'directory' => $controller_dir
        ]);

        if ($res == true) {
            ConsoleOutput::success("Controller successfully created!")->print()->break();
            return true;
        }

        ConsoleOutput::error("Error: Controller already exists!")->print()->break();
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
            ->setFinal()
            ->setExtends(Controller::class)
            ->addMember($construct)
            ->addMember($home_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Components\Controller"))
            ->add($class)
            ->addUse(Controller::class);

        return $data;
    }

    /**
     * @param  mixed $controller_name
     * @return string
     */
    private function codeResourceGenerated(string $controller_name): string
    {
        $index_method = (new Method('index'))
            ->setPublic()
            ->setBody("echo 'index';\nreturn null;")
            ->setReturnType('mixed')
            ->addComment("@return mixed");

        $show_method = (new Method('show'))
            ->setPublic()
            ->setBody('echo "show " . $id;' . "\nreturn null;")
            ->setReturnType('mixed')
            ->addComment('@param mixed $id'."\n@return null|string");

        $show_method->addParameter('id');

        $store_method = (new Method('store'))
            ->setPublic()
            ->setBody("echo 'store';\nreturn null;")
            ->setReturnType('mixed')
            ->addComment("@return mixed");

        $create_method = (new Method('create'))
            ->setPublic()
            ->setBody("echo 'create';\nreturn null;")
            ->setReturnType('mixed')
            ->addComment("@return mixed");

        $edit_method = (new Method('edit'))
            ->setPublic()
            ->setBody('echo "edit " . $id;' . "\nreturn null;")
            ->setReturnType('mixed')
            ->addComment('@param mixed $id'."\n@return mixed");

        $edit_method->addParameter('id');

        $update_method = (new Method('update'))
            ->setPublic()
            ->setBody('echo "update " . $id;' . "\nreturn null;")
            ->setReturnType('mixed')
            ->addComment('@param mixed $id'."\n@return mixed");

        $update_method->addParameter('id');

        $destroy_method = (new Method('destroy'))
            ->setPublic()
            ->setBody('echo "destroy " . $id;' . "\nreturn null;")
            ->setReturnType('mixed')
            ->addComment('@param mixed $id'."\n@return mixed");

        $destroy_method->addParameter('id');

        $class = (new ClassType($controller_name))
            ->setImplements([ResourceControllerInterface::class])
            ->setExtends(Controller::class)
            ->addMember($index_method)
            ->addMember($show_method)
            ->addMember($store_method)
            ->addMember($create_method)
            ->addMember($edit_method)
            ->addMember($update_method)
            ->addMember($destroy_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Components\Controller"))
            ->add($class)
            ->addUse(ResourceControllerInterface::class)
            ->addUse(Controller::class);

        return $data;
    }
}
