<?php

namespace Solital\Core\Kernel\Console\Commands;

use Katrina\Katrina;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Nette\PhpGenerator\{ClassType, PhpNamespace, Property};

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

        if (isset($options->remove)) {
            $this->removeComponent($model_dir, $arguments->model_name . ".php");
        }

        if (!isset($arguments->model_name)) {
            $this->error("Error: Model name not found")->print()->break();
            return false;
        }

        $data = $this->codeGenerated($arguments->model_name);

        $res = $this->createComponent($data, [
            'component_name' => $arguments->model_name,
            'directory' => $model_dir
        ]);

        if ($res == true) {
            $this->success("Model successfully created!")->print()->break();
            return true;
        }

        $this->error("Error: Model already exists!")->print()->break();
        return false;
    }

    /**
     * @param  mixed $model_name
     * @return string
     */
    private function codeGenerated(string $model_name): string
    {
        $table_property = (new Property('table'))
            ->setType('?string')
            ->setValue("")
            ->setProtected()
            ->addComment("\n@var string|null\n");

        $id_property = (new Property('id'))
            ->setType('?string')
            ->setValue("")
            ->setProtected()
            ->addComment("\n@var string|null\n");

        $timestamp_property = (new Property('timestamp'))
            ->setType('bool')
            ->setValue(false)
            ->setProtected()
            ->addComment("\n@var bool\n");

        $class = (new ClassType($model_name))
            ->setExtends(Katrina::class)
            ->addMember($table_property)
            ->addMember($id_property)
            ->addMember($timestamp_property)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Components\Model"))
            ->add($class)
            ->addUse(Katrina::class);

        return $data;
    }
}
