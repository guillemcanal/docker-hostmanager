<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

class DomainNamesAdded extends DomainNamesEvent
{
    public function getName(): string
    {
        return 'domain.names.added';
    }
}
