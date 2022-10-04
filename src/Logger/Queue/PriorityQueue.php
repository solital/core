<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Queue;

class PriorityQueue implements \IteratorAggregate, \Countable
{
    /**
     * data storage
     *
     * @var  array
     */
    protected array $queue = [];

    /**
     * marker for sorted or not
     *
     * @var  bool
     */
    protected bool $sorted = true;

    /**
     * counter for priority
     *
     * @var  int
     */
    protected int $counter = 20000000;

    /**
     * Insert data into the queue with priority
     *
     * @param  mixed $data
     * @param  int   $priority  priority, higher number retrieved first(-1000 - 1000)
     * @return void
     * @throws \RuntimeException if priority out of range
     */
    public function insert(mixed $data, int $priority = 0): void
    {
        $i = $this->getIndex($priority);
        $this->queue[$i] = ['data' => $data, 'priority' => $priority];
        $this->sorted = FALSE;
    }

    /**
     * Combine with queue and return a combined new queue
     *
     * @param  PriorityQueueInterface $queue
     * @return PriorityQueueInterface
     */
    public function combine(PriorityQueue $queue): PriorityQueue
    {
        $nqueue = clone $this;
        foreach ($queue->queue as $data) {
            $nqueue->insert($data['data'], $data['priority']);
        }
        return $nqueue;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->queue);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \ArrayIterator
    {
        $this->sortQueue();
        return new \ArrayIterator(array_column($this->queue, 'data'));
    }

    /**
     * Generate an integer key
     *
     * @param  int $priority
     * @return int
     * @throws \RuntimeException  priority out of range
     */
    protected function getIndex(int $priority): int
    {
        if (abs($priority) > 1000) {
            throw new \RuntimeException("Priority $priority out of range.");
        }
        return --$this->counter + $priority * 10000;
    }

    /**
     * Sort the queue from higher to lower int $key
     *
     * @return $this
     */
    protected function sortQueue()
    {
        if (!$this->sorted) {
            krsort($this->queue);
            $this->sorted = TRUE;
        }
        return $this;
    }
}
