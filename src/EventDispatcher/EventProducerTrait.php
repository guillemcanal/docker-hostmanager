<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

trait EventProducerTrait
{
    private $producedEvents = [];

    public function producedEvents(): array
    {
        $events = $this->producedEvents;
        $this->producedEvents = [];

        return $events;
    }

    private function produceEvent($event): void
    {
        if (!\is_object($event)) {
            throw new \InvalidArgumentException('Produced event should be of type object. Got '.\gettype($event));
        }
        $this->producedEvents[] = $event;
    }
}
