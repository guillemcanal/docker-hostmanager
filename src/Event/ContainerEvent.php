<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

abstract class ContainerEvent implements Event
{
    private $containerName;
    private $domainNames;

    public function __construct($containerName, array $domainNames)
    {
        $this->containerName = $containerName;
        $this->domainNames = $domainNames;
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }

    public function toArray(): array
    {
        return [
            'containerName' => $this->containerName,
            'domainNames' => $this->domainNames,
        ];
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function getDomainNames(): array
    {
        return $this->domainNames;
    }
}
