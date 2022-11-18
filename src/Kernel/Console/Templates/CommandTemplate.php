<?php

/**
 * @generated class generated using Vinci Console
 */

namespace Solital\Console\Command;

use Solital\Core\Console\{Command, MessageTrait};
use Solital\Core\Console\Interface\CommandInterface;

class NameDefault extends Command implements CommandInterface
{
    use MessageTrait;

    /**
     * @var string
     */
    protected string $command = "";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        return $this;
    }
}
