<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\{Interface\CommandInterface, Command};
use Solital\Core\Console\Interface\ExtendCommandsInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Mail\Mailer;
use Solital\Core\FileSystem\HandleFiles;
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace, Property};

class AppStatus extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "status";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Check app status";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $res = Application::appStatus();

        if (!empty($res['message'])) {
            $this->warning('There are alerts you need to see')->print()->break(true);

            foreach ($res['message'] as $key => $message) {
                $this->info($key . ': ')->print();
                $this->line($message)->print()->break();
            }

            return true;
        }

        $this->success('All right, you can now start creating your projects!')->print()->break();
        return true;
    }
}
