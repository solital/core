<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Queue;

use Solital\Core\Logger\Queue\PriorityQueue;

class UniquePriorityQueue extends PriorityQueue
{
    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->getUnique(array_column($this->queue, 'data')));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \ArrayIterator
    {
        $this->sortQueue();
        return new \ArrayIterator(
            $this->getUnique(array_column($this->queue, 'data'))
        );
    }

    /**
     * Remove duplicated items
     *
     * @param  mixed[] $input
     * @return mixed[]
     */
    protected function getUnique(array $input): array
    {
        $result = [];
        foreach ($input as $val) {
            $key = $this->getKey($val);
            if (!isset($result[$key])) {
                $result[$key] = $val;
            }
        }
        return \array_values($result);
    }

    /**
     * Generate related key base on value
     *
     * @param  mixed $val
     * @return string
     */
    protected function getKey(mixed $val): string
    {
        if (is_object($val)) {
            $key = \spl_object_hash($val);
        } elseif (is_scalar($val)) {
            $key = (string) $val;
        } else {
            $key = md5(\serialize($val));
        }
        return $key;
    }
}
