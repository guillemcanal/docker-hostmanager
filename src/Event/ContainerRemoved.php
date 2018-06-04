<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

class ContainerRemoved extends ContainerEvent
{
    public function getName(): string
    {
        return 'container.removed';
    }
}
