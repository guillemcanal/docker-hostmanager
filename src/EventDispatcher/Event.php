<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

interface Event
{
    public function getName(): string;

    public function toArray(): array;

    public function getType(): EventType;
}
