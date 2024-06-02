<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Http\Request;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Course\{Router, RouterBootManagerInterface};
use Solital\Core\Kernel\{Console\HelpersTrait, Application, DebugCore};
use Nette\PhpGenerator\{ClassType, Method, Parameter, PhpNamespace};

class MakeBoot extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:boot";

    /**
     * @var array
     */
    protected array $arguments = ["boot_name"];

    /**
     * @var string
     */
    protected string $description = "Create a command";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $command_dir = Application::getRootApp('BootManager/', DebugCore::isCoreDebugEnabled());

        if (isset($options->remove)) {
            $this->removeComponent($command_dir, $arguments->boot_name . ".php");
        }

        if (!isset($arguments->boot_name)) {
            ConsoleOutput::error("Error: Bootmanager name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->boot_name);
        $res = $this->createComponent($data, [
            'component_name' => $arguments->boot_name,
            'directory' => $command_dir
        ]);

        if ($res == true) {
            ConsoleOutput::success("Bootmanager successfully created!")->print()->break();
            return true;
        } else {
            ConsoleOutput::error("Error: Bootmanager already exists!")->print()->break();
            return false;
        }

        return $this;
    }

    /**
     * @param  mixed $boot_name
     * @return string
     */
    private function codeGenerated(string $boot_name): string
    {
        $boot_method = (new Method('boot'))
            ->setPublic()
            ->setBody('// ...')
            ->setReturnType('void')
            ->setParameters([
                (new Parameter('router'))->setType(Router::class),
                (new Parameter('request'))->setType(Request::class)
            ])
            ->addAttribute(\Override::class)
            ->addComment("Called when router is booting and before the routes is loaded\n\n@param Router " . '$router' . "\n@param Request " . '$request' . "\n@return void");

        $class = (new ClassType($boot_name))
            ->addImplement(RouterBootManagerInterface::class)
            ->addMember($boot_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\BootManager"))
            ->add($class)
            ->addUse(Router::class)
            ->addUse(Request::class)
            ->addUse(RouterBootManagerInterface::class);

        return $data;
    }
}
