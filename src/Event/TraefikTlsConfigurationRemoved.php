<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class TraefikTlsConfigurationRemoved implements Event
{
    private $containerName;

    public function __construct($containerName)
    {
        $this->containerName = $containerName;
    }

    public function getName(): string
    {
        return 'traefik.tls.configuration.removed';
    }

    public function toArray(): array
    {
        return ['containerName' => $this->getContainerName()];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }

    public function getContainerName()
    {
        return $this->containerName;
    }
}
