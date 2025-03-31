<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Schedule\Attribute\EveryMinute;
use Solital\Core\Kernel\{Application, DebugCore};
use Solital\Core\Schedule\{ScheduleInterface, TaskSchedule};
use Nette\PhpGenerator\{ClassType, Method, PhpNamespace};

class MakeSchedule extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "create:schedule";

    /**
     * @var array
     */
    protected array $arguments = ["schedule_name"];

    /**
     * @var string
     */
    protected string $description = "Create a schedule class";

    /**
     * @var mixed
     */
    private mixed $handle;

    /**
     * @var string
     */
    private string $schedule_dir = '';

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        $this->handle = new HandleFiles();
        $this->schedule_dir = Application::getRootApp('Schedule/', DebugCore::isCoreDebugEnabled());

        if (isset($options->remove)) $this->removeComponent($this->schedule_dir, $arguments->schedule_name . ".php");
        if (isset($arguments->schedule_name)) return $this->createSchedule(ucfirst($arguments->schedule_name));

        return $this;
    }

    /**
     * @param  mixed $controller_name
     * @return string
     */
    private function codeGenerated(string $controller_name): string
    {
        $handle_method = (new Method('handle'))
            ->setPublic()
            ->setBody('return $this;')
            ->setReturnType('mixed')
            ->addComment("@return mixed");

        $constructor = (new Method('__construct'))
            ->setPublic()
            ->setBody('$this->time = "everyMinute";')
            ->addComment("Construct with schedule time");

        $class = (new ClassType($controller_name))
            ->setExtends(TaskSchedule::class)
            ->addImplement(ScheduleInterface::class)
            ->addMember($constructor)
            ->addMember($handle_method)
            ->addAttribute(EveryMinute::class)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Schedule"))
            ->add($class)
            ->addUse(ScheduleInterface::class)
            ->addUse(TaskSchedule::class)
            ->addUse(EveryMinute::class);

        return $data;
    }

    /**
     * Create schedule class in app/Schedule
     * 
     * @param string $schedule_name
     * 
     * @return bool
     */
    private function createSchedule(string $schedule_name): bool
    {
        $data = $this->codeGenerated($schedule_name);

        $res = $this->createComponent($data, [
            'component_name' => $schedule_name,
            'directory' => $this->schedule_dir
        ]);

        if ($res == true) {
            ConsoleOutput::success("Schedule successfully created!")->print()->break();
            return true;
        }

        ConsoleOutput::error("Error: Schedule already exists!")->print()->break();
        return false;
    }
}
