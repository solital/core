<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Processor;

use Solital\Core\Logger\Queue\UniquePriorityQueue;

trait ProcessorAwareTrait
{
    use VerifyCallableTrait;

    /**
     * @var bool
     */
    protected bool $isProcessed = false;

    /**
     * @var UniquePriorityQueue[]
     */
    protected static array $processors = [];

    /**
     * Execute all the processors
     *
     * @return self
     */
    public function process(): self
    {
        if (!$this->isProcessed) {
            foreach (static::getProcessors() as $processor) {
                $processor($this);
            }

            $this->isProcessed = true;
        }
        return $this;
    }

    /**
     * Set processors for THIS class
     *
     * @param callable ...$callables
     * 
     * @return string called class name
     * @throws \LogicException if not valid processor found
     */
    public static function addProcessor(callable ...$callables): string
    {
        static::initProcessors();

        $class = \get_called_class();
        $queue = self::$processors[$class];

        foreach ($callables as $callable) {
            static::verifyCallable($callable);
            $queue->insert($callable);
        }

        return $class;
    }

    /**
     * get related processors
     *
     * @return iterable
     */
    protected static function getProcessors(): iterable
    {
        $queue = new UniquePriorityQueue();

        /** @var string $class */
        foreach (static::getClassTree() as $class) {
            if (\method_exists($class, 'initProcessors')) {
                $class::initProcessors();
            }
            $queue = $queue->combine(self::$processors[$class]);
        }

        return $queue;
    }

    /**
     * convert predefined processors from classProcessors()
     */
    protected static function initProcessors(): void
    {
        $class = \get_called_class();

        if (!isset(self::$processors[$class])) {
            self::$processors[$class] = new UniquePriorityQueue();
            foreach (static::classProcessors() as $processor) {
                static::addProcessor($processor);
            }
        }
    }

    /**
     * Define processors for THIS class
     *
     * @return array
     */
    protected static function classProcessors(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected static function getClassTree(): array
    {
        $tree = [];
        $class = \get_called_class();

        while ($class) {
            $tree[] = $class;
            $class = \get_parent_class($class);
        }

        return \array_reverse($tree);
    }
}
