<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\{Interface\CommandInterface, Command};
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;

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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $res = Application::appStatus();

        if (!empty($res['message'])) {
            ConsoleOutput::warning('There are alerts you need to see')->print()->break(true);

            foreach ($res['message'] as $key => $message) {
                ConsoleOutput::info($key . ': ')->print();
                ConsoleOutput::line($message)->print()->break();
            }

            return true;
        }

        ConsoleOutput::success('All right, you can now start creating your projects!')->print()->break();
        return true;
    }
}
