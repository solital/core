<?php

namespace Solital\Core\Kernel\Console\Commands;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Schedule\Schedule;
use Solital\Core\Schedule\ScheduleInterface;

class MakeSchedule extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "schedule";

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
     * Construct
     */
    public function __construct()
    {
        //$this->handle = new HandleFiles;
        $this->handle = Application::provider('handler-file');
    }

    /**
     * @param object $arguments
     * @param object $options
     * 
     * @return mixed
     */
    public function handle(object $arguments, object $options): mixed
    {
        $this->schedule_dir = Application::getRootApp('Schedule/', Application::DEBUG);
        
        if (isset($options->remove)) {
            $this->removeComponent($this->schedule_dir, $arguments->schedule_name . ".php");
        }

        if (!isset($arguments->schedule_name)) {
            $this->error("Error: Schedule name not found")->print()->break();
            return false;
        }

        if (isset($arguments->schedule_name)) {
            return $this->createSchedule(ucfirst($arguments->schedule_name));
        }

        if (isset($options->work) || isset($options->run)) {
            $this->startSchedule($options);
        }

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
            ->setExtends(Schedule::class)
            ->addImplement(ScheduleInterface::class)
            ->addMember($constructor)
            ->addMember($handle_method)
            ->addComment("@generated class generated using Vinci Console");

        $data = (new PhpNamespace("Solital\Schedule"))
            ->add($class)
            ->addUse(ScheduleInterface::class)
            ->addUse(Schedule::class);

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
            $this->success("Schedule successfully created!")->print()->break();
            return true;
        }

        $this->error("Error: Schedule already exists!")->print()->break();
        return false;
    }

    /**
     * @param mixed $options
     * 
     * @return void
     */
    private function startSchedule(mixed $options): void
    {
        Application::classLoaderInDirectory($this->schedule_dir);

        $schedule_files_class = $this->handle->folder($this->schedule_dir)->files();

        foreach ($schedule_files_class as $class) {
            $class = "\Solital\Schedule\\" . basename($class, '.php');

            $reflection_schedule = new \ReflectionClass($class);
            $instance = $reflection_schedule->newInstance();
            $schedules[] = $instance;
        }

        $schedule = new Schedule();
        $schedule->add($schedules);

        if (isset($options->work)) {
            $schedule->execute(true);
        }

        if (isset($options->run)) {
            $schedule->execute();
        }
    }
}
