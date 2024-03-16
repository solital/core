<?php

namespace Solital\Core\Queue;

use Fiber;

class EventLoop
{
    /**
     * @var array
     */
    private array $callStrack = [];

    /**
     * Sleep a Fiber in seconds
     * 
     * @param float $seconds
     * 
     * @return void
     */
    public function sleep(float $seconds): void
    {
        $stop = microtime(true) + $seconds;
        while (microtime(true) < $stop) {
            $this->next();
        }
    }

    /**
     * Suspend a Fiber and next
     * 
     * @param mixed|null $value
     * 
     * @return Fiber|null
     */
    public function next(mixed $value = null): ?Fiber
    {
        return Fiber::suspend($value);
    }

    /**
     * Add a callable to a Fiber
     * 
     * @param callable $callable
     * 
     * @return void
     */
    public function defer(callable $callable): void
    {
        $this->callStrack[] = new Fiber($callable);
    }

    /**
     * Add a callable to a Fiber with time
     * 
     * @param  float     $seconds
     * @param  callable  $callback
     *
     * @return void
     */
    public function deferWithTimer(float $seconds, callable $callback): void
    {
        $this->defer(function () use ($seconds, $callback) {
            $this->sleep($seconds);
            call_user_func($callback);
        });
    }

    /**
     * Execute all Fibers
     * 
     * @return void
     */
    public function run(): void
    {
        while ($this->callStrack != []) {
            foreach ($this->callStrack as $id => $fiber) {
                $this->callFiber($id, $fiber);
            }
        }
    }

    /**
     * @param int $id
     * @param Fiber $fiber
     * 
     * @return Fiber|null
     */
    protected function callFiber(int $id, Fiber $fiber): ?Fiber
    {
        if ($fiber->isStarted() === false) {
            return $fiber->start($id);
        }

        if ($fiber->isTerminated() === false) {
            return $fiber->resume();
        }

        unset($this->callStrack[$id]);
        return $fiber->getReturn();
    }
}
