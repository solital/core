<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Symfony\Component\Yaml\Yaml;
use Phoole\Base\Queue\UniquePriorityQueue;
use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Entry\LogLevelTrait;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Processor\VerifyCallableTrait;

/**
 * HandlerAwareTrait
 *
 * @package   Solital\Core\Logger
 * @interface HandlerAwareInterface
 */
trait HandlerAwareTrait
{
    use LogLevelTrait;
    use VerifyCallableTrait;

    /**
     * queue for the handlers
     *
     * @var  UniquePriorityQueue[][]
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $handlerCache = [];

    /**
     * @param string $level
     * @param callable $handler
     * @param string $entryClass
     * @param int $priority
     * 
     * @return self
     */
    public function addHandler(
        string $level,
        callable $handler,
        $entryClass = LogEntryInterface::class,
        int $priority = 50
    ): self {
        // verify parameters
        $requiredClass = self::verifyCallable($handler, LogEntryInterface::class);
        $entryClass = $this->checkEntry($entryClass, $requiredClass);

        // add handler
        $q = $this->handlers[$level][$entryClass] ?? new UniquePriorityQueue();
        $q->insert($handler, $priority);
        $this->handlers[$level][$entryClass] = $q;

        // clear cache
        $this->handlerCache = [];

        return $this;
    }

    /**
     * Get handlers handling $level and $entry type
     *
     * @param  LogEntryInterface $entry
     * 
     * @return \Traversable
     */
    protected function getHandlers(LogEntryInterface $entry): \Traversable
    {
        // check cache
        $level = $entry->getLevel();
        if (isset($this->handlerCache[$level][\get_class($entry)])) {
            return $this->handlerCache[$level][\get_class($entry)];
        }

        // find matching handlers
        $queue = $this->findHandlers($entry, $level);

        // update cache
        $this->handlerCache[$level][\get_class($entry)] = $queue;

        return $queue;
    }

    /**
     * @param  string|object $entryClass
     * @param  string        $requiredClass
     * 
     * @return string
     * @throws \InvalidArgumentException if not valid message entry
     */
    protected function checkEntry($entryClass, string $requiredClass): string
    {
        if (!\is_a($entryClass, $requiredClass, TRUE)) {
            throw new \InvalidArgumentException("not a valid entry " . $requiredClass);
        }

        return \is_string($entryClass) ? $entryClass : \get_class($entryClass);
    }

    /**
     * @param  LogEntryInterface $entry
     * @param  string            $level
     * 
     * @return UniquePriorityQueue
     */
    protected function findHandlers(LogEntryInterface $entry, string $level): UniquePriorityQueue
    {
        $queue = new UniquePriorityQueue();
        foreach ($this->handlers as $l => $qs) {
            if (!$this->matchLevel($level, $l)) {
                continue;
            }

            /** @var  UniquePriorityQueue $q */
            foreach ($qs as $class => $q) {
                if (is_a($entry, $class)) {
                    $queue = $queue->combine($q);
                }
            }
        }

        return $queue;
    }

    /**
     * return TRUE if can handle the entry level
     *
     * @param  string $entryLevel
     * @param  string $handlerLevel
     * 
     * @return bool
     */
    protected function matchLevel(string $entryLevel, string $handlerLevel): bool
    {
        if ($this->convert[$entryLevel] >= $this->convert[$handlerLevel]) {
            return true;
        }

        return false;
    }
}
