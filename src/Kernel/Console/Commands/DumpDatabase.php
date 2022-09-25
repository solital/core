<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Database\Dump;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;

class DumpDatabase extends Command implements CommandInterface
{
    /**
     * @var string
     */
    protected string $command = "db:dump";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Dump the connected database";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $dir_dump = Application::getRootApp("Storage/dump/", Application::DEBUG);

        (new Dump())->dumpDatabase($dir_dump);

        return $this;
    }
}