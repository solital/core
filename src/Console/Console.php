<?php

namespace Solital\Core\Console;

use Solital\Core\Console\Style\Colors;
use Solital\Core\Console\Command\Commands;
use Solital\Core\Console\Command\FileCommands;
use Solital\Core\Console\Command\SystemCommands;

class Console
{
    /**
     * @var instance
     */
    private $cmd;

    /**
     * @var instance
     */
    private $cmd_system;

    /**
     * @var instance
     */
    private $files;

    /**
     * @var instance
     */
    protected $color;

    /**
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->cmd = new Commands($debug);
        $this->cmd_system = new SystemCommands($debug);
        $this->files = new FileCommands($debug);
        $this->color = new Colors();
    }

    /**
     * @param string $command
     * @param string $file_create
     * @param string $folder
     * 
     * @return void
     */
    public function execComponent(string $command, string $file_create, string $folder): Console
    {
        $cmd = $this->cmd_system->register()->componentsRegistered();

        if (in_array($command, $cmd['cmd'])) {
            if (strpos($command, 'remove') === false) {
                $this->cmd->$command($file_create)->createComponent();

                die;
            } else {
                $method = $this->execute();

                if (empty($method[$command])) {
                    $msg = $this->color->stringColor("Vinci: Command not found", "yellow", "red", true);
                    print_r($msg);

                    die;
                }

                $method = $method[$command];

                $execute_method1 = explode(':', $command);
                $function = $execute_method1[0];
                $method2 = explode('remove-', $function);
                $execute_method2 = $method2[1];

                $this->files->confirmDialog("Do you really want to remove this component?[Y/N]");

                $this->cmd->$execute_method2($file_create)->removeComponent();

                die;
            }
        } else {
            $msg = $this->color->stringColor("Vinci: Command not found", "yellow", "red", true);
            print_r($msg);
        }

        return $this;
    }

    /**
     * @param mixed $command
     * 
     * @return void
     */
    public function execCommand($command): Console
    {
        $cmd = $this->cmd_system->register()->commandsRegistered();

        if (in_array($command, $cmd['cmd'])) {
            $method = $this->execute();
            $execute_method = $method[$command];

            if (method_exists($this->cmd_system, $execute_method)) {
                $this->cmd_system->$execute_method();
            } else {
                $this->files->$execute_method();
            }
        } else {
            $msg = $this->color->stringColor("Vinci: Command not found", "yellow", "red", true);
            print_r($msg);
        }

        return $this;
    }

    /**
     * @param string $cmd
     * 
     * @return array
     */
    private function execute(): array
    {
        return [
            'controller' => 'controller:file_name',
            'model' => 'model:file_name',
            'view' => 'view:file_name',
            'router' => 'file:file_name',
            'js' => 'js:file_name',
            'css' => 'css:file_name',
            'remove-controller' => 'controller:file_name',
            'remove-model' => 'model:file_name',
            'remove-view' => 'view:file_name',
            'remove-router' => 'file:file_name',
            'remove-js' => 'js:file_name',
            'remove-css' => 'css:file_name',
            'version' => 'version',
            'show' => 'show',
            'routes' => 'routes',
            'cache-clear' => 'clearCache',
            'session-clear' => 'clearSession',
            'login' => 'login',
            'remove-login' => 'removeLogin',
            'forgot' => 'forgot',
            'remove-forgot' => 'removeForgot',
            'minify-css' => 'minifyCss',
            'minify-js' => 'minifyJs',
            'minify-all' => 'minifyAll'
        ];
    }
}
