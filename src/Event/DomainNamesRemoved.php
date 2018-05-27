<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

class DomainNamesRemoved extends DomainNamesEvent
{
    public function getName(): string
    {
        return 'domain.names.removed';
    }
}
