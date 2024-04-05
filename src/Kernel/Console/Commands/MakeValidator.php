<?php

namespace Solital\Core\Kernel\Console\Commands;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Solital\Core\Console\Command;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Http\RequestValidatorInterface;
use Solital\Core\Kernel\{Application, DebugCore};

class MakeValidator extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:validator";

    /**
     * @var array
     */
    protected array $arguments = ["validator_name"];

    /**
     * @var string
     */
    protected string $description = 'Create a request $_POST and $_FILES validator';

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $validator_dir = Application::getRootApp('Components/Validator/', DebugCore::isCoreDebugEnabled());

        if (isset($options->remove)) {
            $this->removeComponent($validator_dir, $arguments->validator_name . ".php");
        }

        if (!isset($arguments->validator_name)) {
            ConsoleOutput::error("Error: Validator name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->validator_name);
        $res = $this->createComponent($data, [
            'component_name' => $arguments->validator_name,
            'directory' => $validator_dir
        ]);

        if ($res == true) {
            ConsoleOutput::success("Validator successfully created!")->print()->break();
            return true;
        }

        ConsoleOutput::error("Error: Validator already exists!")->print()->break();
        return false;
    }

    /**
     * @param  mixed $validator_name
     * @return string
     */
    private function codeGenerated(string $validator_name): string
    {
        $rules_method = (new Method('rules'))
            ->setPublic()
            ->setBody("return [];")
            ->setReturnType('array')
            ->addComment("Set validation rules\n@return array");

        $filters_method = (new Method('filters'))
            ->setPublic()
            ->setBody("return [];")
            ->setReturnType('array')
            ->addComment("Set filter rules\n@return array");

        $messages_method = (new Method('messages'))
            ->setPublic()
            ->setBody("return [];")
            ->setReturnType('array')
            ->addComment("Set field-rule specific error messages\n@return array");

        $class = (new ClassType($validator_name))
            ->addMember($rules_method)
            ->addMember($filters_method)
            ->addMember($messages_method)
            ->addImplement(RequestValidatorInterface::class)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Components\Validator"))
            ->add($class)
            ->addUse(RequestValidatorInterface::class);

        return $data;
    }
}
