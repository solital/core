<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Course\CourseList;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Kernel\DebugCore;

class HandleCourse extends Command implements CommandInterface
{
    /**
     * @var string
     */
    protected string $command = "router:list";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Show all routes";

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        if (DebugCore::isCoreDebugEnabled() == true) {
            ConsoleOutput::error("Debug enabled! You cannot run this command.")->print()->break()->print();
            return false;
        }

        if (!isset($_SERVER["REQUEST_METHOD"]) && !isset($_SERVER["REQUEST_URI"])) {
            $_SERVER["REQUEST_METHOD"] = "GET";
            $_SERVER["REQUEST_URI"] = "/";
        }

        CourseList::start(true);
        return $this;
    }
}
