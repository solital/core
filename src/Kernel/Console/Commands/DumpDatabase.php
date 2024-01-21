<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Database\Dump\Dump;
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
        Application::connectionDatabase();

        $dir_dump = Application::getRootApp("Storage/dump/", true);
        $dump = new Dump();

        if (isset($options->exclude)) {
            $dump->excludeTables($options->exclude);
        }

        $dump->dumpDatabase($dir_dump);
        $this->success("Dump Database created successfully!")->print()->break()->exit();

        return $this;
    }
}
