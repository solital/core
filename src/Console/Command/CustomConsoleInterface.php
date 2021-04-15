<?php

namespace Solital\Core\Console\Command;

interface CustomConsoleInterface
{
    /**
     * @return array
     */
    public function execute(): array;
}
