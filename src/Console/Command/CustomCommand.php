<?php

namespace Solital\Core\Console\Command;

use Solital\Core\Console\Command\SystemCommands;
use Solital\Core\Console\Command\CustomCommandTrait;

class CustomCommand extends SystemCommands
{
    use CustomCommandTrait;

    /**
     * @param string $cmd
     * 
     * @return mixed
     */
    public function exec(string $cmd)
    {
        $this->execCommand($cmd);
    }
}
