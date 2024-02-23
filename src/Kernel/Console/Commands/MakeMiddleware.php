<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace};
use Solital\Core\Http\Middleware\BaseMiddlewareInterface;

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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $middleware_dir = Application::getRootApp('Middleware/', Application::DEBUG);

        if (isset($options->remove)) {
            $this->removeComponent($middleware_dir, $arguments->middleware_name . ".php");
        }

        if (!isset($arguments->middleware_name)) {
            $this->error("Error: Middleware name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->middleware_name);

        $res = $this->createComponent($data, [
            'component_name' => $arguments->middleware_name,
            'directory' => $middleware_dir
        ]);

        if ($res == true) {
            $this->success("Middleware successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Middleware already exists!")->print()->break();

            return false;
        }

        return $this;
    }

    /**
     * @param  mixed $controller_name
     * @return string
     */
    private function codeGenerated(string $controller_name): string
    {
        $handle_method = (new Method('handle'))
            ->setPublic()
            ->setBody("// ...")
            ->setReturnType('void')
            ->addComment("@return void");

        $class = (new ClassType($controller_name))
            ->addImplement(BaseMiddlewareInterface::class)
            ->addMember($handle_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Middleware"))
            ->add($class)
            ->addUse(BaseMiddlewareInterface::class);

        return $data;
    }
}
