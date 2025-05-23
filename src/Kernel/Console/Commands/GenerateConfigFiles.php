<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Mail\Mailer;
use Solital\Core\Queue\Queue;
use Solital\Core\Console\Command;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Console\Interface\{ExtendCommandsInterface, CommandInterface};
use Solital\Core\Kernel\{Application, Console\HelpersTrait, DebugCore};
use Solital\Core\Container\Interface\{ContainerInterface, ServiceProviderInterface};
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace, Property};

class GenerateConfigFiles extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "generate:files";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Imports Solital Framework's default configuration files";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $config_app_dir = Application::getRootApp('config/', DebugCore::isCoreDebugEnabled());
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config';

        if (isset($options->component) && isset($options->component)) {
            $arguments_default = (object)[
                'controller_name' => $options->component . 'Controller',
                'model_name' => $options->component,
                'migration_name' => 'create_' . strtolower($options->component),
                'seeder_name' => $options->component . 'Seed'
            ];

            $controller = new MakeController();
            $controller->handle($arguments_default, $options);

            $model = new MakeModel();
            $model->handle($arguments_default, $options);

            $migration = new MakeMigrations();
            $migration->handle($arguments_default, $options);

            $seeder = new MakeSeeder();
            $seeder->handle($arguments_default, $options);

            return true;
        }

        $this->copyFiles($config_core_dir, $config_app_dir);
        $this->queueFiles();
        $this->commandFiles();
        $this->serviceContainerClass();
        ConsoleOutput::success('Configuration files copied successfully!')->print()->break();

        return true;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function queueFiles(): GenerateConfigFiles
    {
        $dir_queue = Application::getRootApp('Queue/', DebugCore::isCoreDebugEnabled());

        $dispatch_method = (new Method('dispatch'))
            ->setPublic()
            ->setBody("(new Mailer)->sendQueue();")
            ->addComment("Send queue email");

        $class = (new ClassType('MailQueue'))
            ->addMember($dispatch_method)
            ->addComment("@generated class generated using Vinci Console")
            ->setExtends(Queue::class);

        $class->addProperty('sleep', 10.0)
            ->setProtected()
            ->setType('float')
            ->addComment('For codes that take a considerable amount of time to execute, change the $sleep variable');

        $data = (new PhpNamespace("Solital\Queue"))
            ->add($class)
            ->addUse(Mailer::class)
            ->addUse(Queue::class);

        $this->createComponent($data, [
            'component_name' => 'MailQueue',
            'directory' => $dir_queue
        ]);

        return $this;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function serviceContainerClass(): GenerateConfigFiles
    {
        $provider_dir = Application::getRootApp('', DebugCore::isCoreDebugEnabled());

        $register_method = (new Method('register'))
            ->setPublic()
            ->setBody("// ...")
            ->addComment("Register all containers");

        $register_method->addParameter('container')->setType(ContainerInterface::class);

        $class = (new ClassType('ServiceContainer'))
            ->addMember($register_method)
            ->addComment("@generated class generated using Vinci Console")
            ->addImplement(ServiceProviderInterface::class);

        $data = (new PhpNamespace("Solital"))
            ->add($class)
            ->addUse(ContainerInterface::class)
            ->addUse(ServiceProviderInterface::class);

        $this->createComponent($data, [
            'component_name' => 'ServiceContainer',
            'directory' => $provider_dir
        ]);

        return $this;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function commandFiles(): GenerateConfigFiles
    {
        $dir_cmd = Application::getRootApp('Console/', DebugCore::isCoreDebugEnabled());

        $command_class = (new Property("command_class"))
            ->setType("array")
            ->setValue([])
            ->setProtected()
            ->addComment("\n@var array\n");

        $get_command_class = (new Method("getCommandClass"))
            ->setPublic()
            ->setBody("\$this->command_class = Application::getUserCommands();\nreturn \$this->command_class;")
            ->setReturnType("array")
            ->addComment("@return array");

        $get_type_command = (new Method("getTypeCommands"))
            ->setPublic()
            ->setBody("return \"User Command\";")
            ->setReturnType("string")
            ->addComment("@return string");

        $class = (new ClassType("Config"))
            ->addImplement(ExtendCommandsInterface::class)
            ->addMember($get_command_class)
            ->addMember($get_type_command)
            ->addMember($command_class)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Console"))
            ->add($class)
            ->addUse(ExtendCommandsInterface::class)
            ->addUse(Application::class);

        $this->createComponent($data, [
            "component_name" => "Config",
            "directory" => $dir_cmd
        ]);

        return $this;
    }

    /**
     * @param string $config_core_dir
     * @param string $template_dir
     * 
     * @return void
     */
    private function copyFiles(string $config_core_dir, string $template_dir): void
    {
        $handle_files = Application::provider('handler-file');
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($template_dir);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $template_dir . $file_name);
        }
    }
}
