<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

class EventSubscription
{
    private $eventClass;
    private $callback;

    public function __construct(string $eventClass, callable $callback)
    {
        if (!\is_a($eventClass, Event::class, true)) {
            throw new \InvalidArgumentException($eventClass . ' does not implements ' . Event::class);
        }

        $this->eventClass = $eventClass;
        $this->callback = $callback;
    }

    public function support(Event $event): bool
    {
        return \is_a($event, $this->eventClass);
    }

    public function handle($event): void
    {
        ($this->callback)($event);
    }
}