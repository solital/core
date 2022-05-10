<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Queue\Queue;

class MakeQueue extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:queue";

    /**
     * @var array
     */
    protected array $arguments = ["queue_name"];

    /**
     * @var string
     */
    protected string $description = "Create a Queue class";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        (new Queue)->create($arguments->queue_name);
        return true;
    }
}