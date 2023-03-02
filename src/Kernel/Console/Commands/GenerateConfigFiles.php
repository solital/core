<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\{Interface\CommandInterface, Command};
use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Mail\Mailer;
use Solital\Core\FileSystem\HandleFiles;
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
    public function handle(object $arguments, object $options): mixed
    {
        $config_app_dir = Application::getRootApp('config/', Application::DEBUG);
        $config_core_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config';

        $this->copyFiles($config_core_dir, $config_app_dir);
        $this->queueFiles();
        $this->commandFiles();
        $this->success('Configuration files copied successfully!')->print()->break();

        return true;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function queueFiles(): GenerateConfigFiles
    {
        $dir_queue = Application::getRootApp('Queue/', Application::DEBUG);

        $dispatch_method = (new Method('dispatch'))
            ->setPublic()
            ->setBody("(new " . Mailer::class . ")->sendQueue();")
            ->addComment("Send queue email");

        $class = (new ClassType('MailQueue'))
            ->addMember($dispatch_method)
            ->addComment("@generated class generated using Vinci Console");

        $this->createComponent($class, [
            'component_name' => 'MailQueue',
            'directory' => $dir_queue
        ]);

        return $this;
    }

    /**
     * @return GenerateConfigFiles
     */
    private function commandFiles(): GenerateConfigFiles
    {
        $dir_cmd = Application::getRootApp('Console/', Application::DEBUG);

        $command_class = (new Property('command_class'))
            ->setType('array')
            ->setValue([])
            ->setProtected()
            ->addComment("\n@var array\n");

        $type_commands = (new Property('type_commands'))
            ->setType('string')
            ->setValue("")
            ->setProtected()
            ->addComment("\n@var string\n");

        $get_command_class = (new Method('getCommandClass'))
            ->setPublic()
            ->setBody('return $this->command_class;')
            ->setReturnType('array')
            ->addComment("@return array");

        $get_type_command = (new Method('getTypeCommands'))
            ->setPublic()
            ->setBody('return $this->type_commands;')
            ->setReturnType('string')
            ->addComment("@return string");

        $class = (new ClassType('Config'))
            ->addImplement(ExtendCommandsInterface::class)
            ->addMember($get_command_class)
            ->addMember($get_type_command)
            ->addMember($command_class)
            ->addMember($type_commands)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Console"))
            ->add($class)
            ->addUse(ExtendCommandsInterface::class);

        $this->createComponent($data, [
            'component_name' => 'Config',
            'directory' => $dir_cmd
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
        $handle_files = new HandleFiles();
        $files = $handle_files->folder($config_core_dir)->files();
        $handle_files->create($template_dir);

        foreach ($files as $file) {
            $file_name = pathinfo($file);
            $file_name = $file_name['basename'];
            $handle_files->copy($file, $template_dir . $file_name);
        }
    }
}
