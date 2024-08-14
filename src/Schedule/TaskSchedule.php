<?php

namespace Solital\Core\Schedule;

use GO\{Scheduler, Traits\Interval};
use Solital\Core\Console\Output\ConsoleOutput;
use Solital\Core\Schedule\Exception\ScheduleException;

class TaskSchedule extends Scheduler
{
    use Interval;

    /**
     * @var Scheduler
     */
    private Scheduler $scheduler;

    /**
     * @var string
     */
    protected string $time = "everyMinute()";

    /**
     * @var string|null
     */
    protected ?string $time_args = null;

    /**
     * @var array
     */
    private array $config = [];

    /**
     * @var null|string
     */
    private ?string $schedule_log_dir = null;

    /**
     * @param array $config
     * 
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Set schedule logs to file
     *
     * @param string $schedule_log_dir
     * 
     * @return self
     */
    public function setScheduleLogs(string $schedule_log_dir): self
    {
        $this->schedule_log_dir = $schedule_log_dir;
        return $this;
    }

    /**
     * Add jobs to schedule
     * 
     * @param array $tasks
     * 
     * @return self
     */
    public function addTasks(array $tasks): self
    {
        try {
            if (!array_is_list($tasks)) throw new ScheduleException("`\$tasks` must not have keys in `add` method");
            if (is_null($this->schedule_log_dir))
                throw new ScheduleException("Schedule log directory is null. Use `setScheduleLogs` method");

            $this->scheduler = new Scheduler($this->config);

            foreach ($tasks as $task) {
                ConsoleOutput::warning("[" . date("Y-m-d H:i:s") . "] Processing job: " . $task)->print()->break();

                $reflection = new \ReflectionClass($task);
                $task_instance = $reflection->newInstance();
                $method = $reflection->getMethod("handle")->getClosure(new $task);
                $attributes = $reflection->getAttributes();
                $time = $reflection->getProperty("time")->getValue(new $task);

                if (!method_exists($this, $time))
                    throw new ScheduleException("Time `" . $time . "` is not valid in `" . $task . "` class");

                $this->processJobs($method, $time, $task_instance, $attributes);
                ConsoleOutput::success("[" . date("Y-m-d H:i:s") . "] Job processed: " . $task)->print()->break();
            }
        } catch (ScheduleException $e) {
            ConsoleOutput::error("[" . date("Y-m-d H:i:s") . "] Error job: " . $e->getMessage())->print()->break()->exit();
        }

        echo PHP_EOL;
        return $this;
    }

    /**
     * Get the queued jobs
     *
     * @return never
     */
    public function getQueuedJobs(): never
    {
        ConsoleOutput::success(
            "[" . date('Y-m-d H:i:s') . "] Queued Jobs: " . count($this->scheduler->getQueuedJobs())
        )->print()->break()->exit();
    }

    /**
     * Get the executed jobs
     * 
     * @return never
     */
    public function getExecutedJobs(): never
    {
        ConsoleOutput::success(
            "[" . date('Y-m-d H:i:s') . "] Executed Jobs: " . count($this->scheduler->getExecutedJobs())
        )->print()->break()->exit();
    }

    /**
     * Run a scheduler or start a worker
     * 
     * @param bool $work
     * 
     * @return void
     */
    public function runTasks(bool $work = false): void
    {
        ConsoleOutput::success(
            "[" . date('Y-m-d H:i:s') . "] Scheduler is executing jobs which are due..."
        )->print()->break();

        ($work === true) ? $this->scheduler->work() : $this->scheduler->run();

        if (!empty($this->getFailedJobs())) {
            $failedJob = $this->scheduler->getFailedJobs()[0];
            $exp = $failedJob->getException();
            $message = "Exception caught in `" . basename($exp->getFile(), ".php") . "` job: " . $exp->getMessage();

            /** ADICIONAR UM MÉTODO DO LOGGER */
            file_put_contents(
                dirname(__DIR__) . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR . "failed-jobs.log",
                "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL,
                FILE_APPEND
            );

            ConsoleOutput::error($message)->print()->break()->exit();
        }
    }

    /**
     * Process all tasks
     */
    private function processJobs(
        object $method,
        string $time_property,
        object $task,
        array $attributes
    ): void {
        $args = null;
        $time_attr = "";

        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), "Solital\Core\Schedule\Attribute\\")) {
                $instance = $attribute->newInstance();
                $time_attr = $instance->getAttributeName();
                $args = $instance->getArgs();
            }
        }

        $job = $this->scheduler->call($method);

        ($time_attr != "") ?
            $job = call_user_func_array([$job, $time_attr], $args) :
            $job = call_user_func([$job, $time_property]);

        if (method_exists($task, "before")) {
            ConsoleOutput::warning("[" . date("Y-m-d H:i:s") . "] Processing `before` method: " . $task::class)->print()->break();
            $job->before(fn() => $task->before());
            ConsoleOutput::success("[" . date("Y-m-d H:i:s") . "] Method `before` processed: " . $task::class)->print()->break();
        }

        if (method_exists($task, "then")) {
            ConsoleOutput::warning("[" . date("Y-m-d H:i:s") . "] Processing `then` method: " . $task::class)->print()->break();
            $job->then(fn() => $task->then());
            ConsoleOutput::success("[" . date("Y-m-d H:i:s") . "] Method `then` processed: " . $task::class)->print()->break();
        }

        if (method_exists($task, "when")) {
            ConsoleOutput::warning("[" . date("Y-m-d H:i:s") . "] Processing `when` method: " . $task::class)->print()->break();
            $job->when(fn() => $task->when());
            ConsoleOutput::success("[" . date("Y-m-d H:i:s") . "] Method `when` processed: " . $task::class)->print()->break();
        }

        $job->output($this->schedule_log_dir . basename($task::class, ".php") . ".log", true);
    }

    /**
     * @param string $class
     * 
     * @return string
     */
    private function getClassName(string $class): string
    {
        $var = explode('\\', $class);
        $className = array_pop($var);
        return lcfirst($className);
    }
}
