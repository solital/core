<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Database\Dump\Dump;
use Solital\Core\Database\Dump\Exception\DumpException;
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
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        Application::connectionDatabase();

        if (Application::DEBUG == false) {
            if (getenv('DB_NAME') == '' || getenv('DB_USER') == '' || getenv('DB_PASS') == '') {
                throw new DumpException('Variables DB_NAME, DB_USER and DB_PASS not configured');
            }
        }

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
