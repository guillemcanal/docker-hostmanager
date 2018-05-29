<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class EventProcessed implements Event
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getName(): string
    {
        return 'event.processed';
    }

    public function toArray(): array
    {
        return ['message' => $this->message];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
