<?php

namespace Solital\Core\Schedule;

use GO\Scheduler;
use GO\Traits\Interval;
use Solital\Core\Console\MessageTrait;
use Solital\Core\Kernel\Application;
use Solital\Core\Schedule\Exception\ScheduleException;

class Schedule extends Scheduler
{
    use Interval;
    use MessageTrait;

    /**
     * @var Scheduler
     */
    private Scheduler $scheduler;

    /**
     * @var mixed
     */
    private mixed $failedJobs;

    /**
     * @var string
     */
    protected string $time;

    /**
     * @var string
     */
    private string $schedule_log_dir;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->schedule_log_dir = Application::getRootApp('Storage/schedules/');
        $this->scheduler = new Scheduler();
    }

    /**
     * @param array $jobs_class
     * 
     * @return Schedule
     */
    public function add(array $jobs_class): Schedule
    {
        foreach ($jobs_class as $class) {
            $recflection_method = new \ReflectionMethod($class::class, 'handle');
            $method_handle = $recflection_method->getClosure($class);

            $recflection_class = new \ReflectionClass($class::class);
            $time = $recflection_class->getProperty('time')->getValue($class);

            if (!method_exists($this, $time)) {
                throw new ScheduleException("Time '" . $time . "' is not valid in " . $class::class);
            }

            //$this->warning("[" . date('Y-m-d H:i:s') . "] Processing job: " . get_class($class))->print()->break();
            $this->warning("[" . date('Y-m-d H:i:s') . "] Processing job: " . $class::class)->print()->break();

            $this->scheduler->call($method_handle)->{$time}()
                ->before(function () use ($class) {
                    if (method_exists($class::class, 'before')) {
                        $this->warning("[" . date('Y-m-d H:i:s') . "] Processing 'before' job: " . $class::class)->print()->break();
                        $class->before();
                        $this->success("[" . date('Y-m-d H:i:s') . "] Job 'before' processed: " . $class::class)->print()->break();
                    }
                })->then(function () use ($class) {
                    if (method_exists($class::class, 'after')) {
                        $this->warning("[" . date('Y-m-d H:i:s') . "] Processing 'after' job: " . $class::class)->print()->break();
                        $class->after();
                        $this->success("[" . date('Y-m-d H:i:s') . "] Job 'after' processed: " . $class::class)->print()->break();
                    }
                })->output($this->schedule_log_dir . basename((string) $class::class, '.php') . '.log', true);

            $this->success("[" . date('Y-m-d H:i:s') . "] Job processed: " . $class::class)->print()->break();
        }

        return $this;
    }

    /**
     * @param bool $work
     * 
     * @return Schedule
     */
    public function execute(bool $work = false): Schedule
    {
        $this->failedJobs = $this->scheduler->getFailedJobs();
        echo PHP_EOL;
        $this->success("[" . date('Y-m-d H:i:s') . "] Scheduler is executing jobs which are due...")->print()->break();

        if ($work === true) {
            $this->scheduler->work();
            return $this;
        }

        $this->scheduler->run();
        return $this;
    }

    /**
     * @return mixed
     */
    public function failedJobs(): mixed
    {
        return $this->failedJobs;
    }
}
