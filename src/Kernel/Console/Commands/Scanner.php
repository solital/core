<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\Scanner\MalwareScanner;

class Scanner extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "scanner";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Find infected files";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $dir = Application::getRoot('', false);

        if (isset($options->all)) {
            $this->scan($dir);
            return true;
        }

        $this->scan($dir . "app" . DIRECTORY_SEPARATOR);
        return true;
    }

    /**
     * @param string $dir
     * 
     * @return void
     */
    private function scan(string $dir): void
    {
        $scan = new MalwareScanner(false);
        $scan->setFlagHideWhitelist(true);
        $scan->setFlagHideOk(true);
        $scan->setFlagNoStop(true);
        $scan->setFlagLineNumber(true);
        $scan->setIgnore([
            'info-server.php',
            'MockClass.php',
            'MockTrait.php',
            'AnsiColorMode.php',
            'ConsoleOutput.php',
            'AbstractUnicodeString.php',
            'Container.php',
            'Process.php'
        ]);
        $scan->run($dir);
    }
}
