<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class SignedCertificateRemoved implements Event
{
    private $containerName;

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }

    public function getName(): string
    {
        return 'signed.certificate.removed';
    }

    public function toArray(): array
    {
        return [
            'containerName' => $this->containerName,
        ];
    }

    public function __construct(string $containerName)
    {
        $this->containerName = $containerName;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }
}
