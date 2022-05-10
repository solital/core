<?php

namespace Solital\Core\Console\Interface;

interface CommandInterface
{
    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed;
}