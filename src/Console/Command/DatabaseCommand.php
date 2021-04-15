<?php

namespace Solital\Core\Console\Command;

use Solital\Core\Console\Style\Colors;
use Solital\Core\Database\Create\Dump;

class DatabaseCommand
{
    /**
     * @return DatabaseCommand
     */
    public function checkConnection(): DatabaseCommand
    {
        if (
            $_ENV['DB_DRIVE'] == "" ||
            $_ENV['DB_HOST'] == "" ||
            $_ENV['DB_NAME'] == "" ||
            $_ENV['DB_USER'] == ""
        ) {
            $msg = (new Colors())->stringColor("Error: Database not connected!", "yellow", "red", true);
            print_r($msg);

            die;
        }

        return $this;
    }

    /**
     * @return DatabaseCommand
     */
    public function dump(): DatabaseCommand
    {
        $dir = SITE_ROOT_VINCI . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "dump" . DIRECTORY_SEPARATOR;

        (new Dump())->dumpDatabase($dir);

        return $this;
    }
}
