<?php

namespace Solital\Core\Kernel\Console\Commands;

use Override;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Kernel\{Application, DebugCore};
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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $command_dir = Application::getRootApp('Console/Command/', DebugCore::isCoreDebugEnabled());

        if (isset($options->remove)) {
            $this->removeComponent($command_dir, $arguments->command_name . ".php");
        }

        if (!isset($arguments->command_name)) {
            ConsoleOutput::error("Error: Command name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->command_name);

        $res = $this->createComponent($data, [
            'component_name' => $arguments->command_name,
            'directory' => $command_dir
        ]);

        if ($res == true) {
            ConsoleOutput::success("Command successfully created!")->print()->break();
            return true;
        } else {
            ConsoleOutput::error("Error: Command already exists!")->print()->break();
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

        $options_property = (new Property('options'))
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
            ->setBody('echo \'Hello World\';' . PHP_EOL . 'return $this;')
            ->setReturnType('mixed')
            ->setParameters([
                (new Parameter('arguments'))->setType('object'),
                (new Parameter('options'))->setType('object')
            ])
            ->addAttribute(Override::class)
            ->addComment("@param object " . '$arguments' . "\n@param object " . '$options' . "\n@return mixed");

        $class = (new ClassType($controller_name))
            ->setExtends(Command::class)
            ->addImplement(CommandInterface::class)
            ->addMember($command_property)
            ->addMember($arguments_property)
            ->addMember($options_property)
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
