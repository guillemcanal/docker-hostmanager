<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class ApplicationStarted implements Event
{
    public function getName(): string
    {
        return 'application.started';
    }

    public function toArray(): array
    {
        return [];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }
}
