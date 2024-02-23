<?php

namespace Solital\Core\Kernel\Console\Commands;

use Solital\Core\Console\Command;
use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Schedule\Schedule as TaskSchedule;

class Schedule extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = "schedule";

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var array
     */
    protected array $options = ["--work", "--run"];

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
        $this->handle = Application::provider('handler-file');
        $this->schedule_dir = Application::getRootApp('Schedule/', Application::DEBUG);

        if (isset($options->remove)) {
            $this->removeComponent($this->schedule_dir, $arguments->schedule_name . ".php");
        }

        if (isset($options->work) || isset($options->run)) {
            $this->startSchedule($options);
        } else {
            $this->error("Error: Schedule name not found")->print()->break();
            return false;
        }

        return $this;
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

        $schedule = new TaskSchedule();
        $schedule->add($schedules);

        if (isset($options->work)) {
            $schedule->execute(true);
        }

        if (isset($options->run)) {
            $schedule->execute();
        }
    }
}
