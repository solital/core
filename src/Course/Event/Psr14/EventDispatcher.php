<?php

namespace Solital\Core\Course\Event\Psr14;

use Psr\EventDispatcher\{
    EventDispatcherInterface,
    ListenerProviderInterface,
    StoppableEventInterface
};

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ListenerProviderInterface $provider
     */
    private ListenerProviderInterface $provider;

    /**
     * @param ListenerProviderInterface $provider
     */
    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param object $event
     * 
     * @return object
     */
    public function dispatch(object $event)
    {
        if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
            return $event;
        }

        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            $listener($event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }

        return $event;
    }
}
