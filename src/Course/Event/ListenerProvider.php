<?php

namespace Solital\Core\Course\Event;

use Fig\EventDispatcher\ParameterDeriverTrait;
use Psr\EventDispatcher\ListenerProviderInterface;
use SplPriorityQueue;

class ListenerProvider implements ListenerProviderInterface
{
    use ParameterDeriverTrait;

    /**
     * @var array
     */
    private array $listeners = [];

    /**
     * @param object $event
     * 
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $queue = new SplPriorityQueue();

        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener['type']) {
                $queue->insert($listener['listener'], $listener['priority']);
            }
        }

        return $queue;
    }

    /**
     * @param callable $listener
     * @param int $priority
     * 
     * @return self
     */
    public function addListener(callable $listener, int $priority = 0): self
    {
        $type = $this->getParameterType($listener);
        $this->listeners[] = [
            'type' => $type,
            'listener' => $listener,
            'priority' => $priority,
        ];

        return $this;
    }
}
