<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Kernel\Application;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Console\HelpersTrait;
use Nette\PhpGenerator\{ClassType, Method, Parameter, PhpNamespace, Property};

class MakeCommand extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:cmd";

    /**
     * @var array
     */
    protected array $arguments = ["command_name"];

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
    public function handle(object $arguments, object $options): mixed
    {
        $command_dir = Application::getRootApp('Console/Command/', Application::DEBUG);

        if (isset($options->remove)) {
            $this->removeComponent($command_dir, $arguments->command_name . ".php");
        }

        $data = $this->codeGenerated($arguments->command_name);

        $res = $this->createComponent($data, [
            'component_name' => $arguments->command_name,
            'directory' => $command_dir
        ]);

        if ($res == true) {
            $this->success("Command successfully created!")->print()->break();

            return true;
        } else {
            $this->error("Error: Command already exists!")->print()->break();

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
        $command_property = (new Property('command'))
            ->setType('string')
            ->setValue("")
            ->setProtected()
            ->addComment("\n@var string\n");

        $arguments_property = (new Property('arguments'))
            ->setType('array')
            ->setValue([])
            ->setProtected()
            ->addComment("\n@var array\n");

        $description_property = (new Property('description'))
            ->setType('string')
            ->setValue("")
            ->setProtected()
            ->addComment("\n@var string\n");

        $handle_method = (new Method('handle'))
            ->setPublic()
            ->setBody('return $this;')
            ->setReturnType('mixed')
            ->setParameters([
                (new Parameter('argumments'))->setType('object'),
                (new Parameter('options'))->setType('object')
            ])
            ->addComment("@param object " . '$arguments' . "\n@param object " . '$options' . "\n@return mixed");

        $class = (new ClassType($controller_name))
            ->setExtends(Command::class)
            ->addImplement(CommandInterface::class)
            ->addMember($command_property)
            ->addMember($arguments_property)
            ->addMember($description_property)
            ->addMember($handle_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Console\Command"))
            ->add($class)
            ->addUse(Command::class)
            ->addUse(CommandInterface::class);

        return $data;
    }
}
