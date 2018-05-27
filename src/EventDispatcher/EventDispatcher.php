<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

class EventDispatcher
{
    private $listeners;

    public function __construct(EventListener ...$listeners)
    {
        $this->listeners = $listeners;
    }

    public function dispatch($event): void
    {
        if (!\is_object($event)) {
            throw new \UnexpectedValueException('The given event should be an object. Got ' . \gettype($event));
        }

        foreach ($this->listeners as $listener) {
            if ($listener->subscription()->support($event)) {
                $listener->subscription()->handle($event);
                if ($listener instanceof EventProducer) {
                    $this->collectAndDispatchEventsFrom($listener);
                }
            }
        }
    }

    private function collectAndDispatchEventsFrom(EventProducer $listener): void
    {
        foreach ($listener->producedEvents() as $producedEvent) {
            $this->dispatch($producedEvent);
        }
    }
}