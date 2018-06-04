<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

class ContainerCreated extends ContainerEvent
{
    private $containerAttributes;

    public function __construct($containerName, array $domainNames, array $containerAttributes)
    {
        parent::__construct($containerName, $domainNames);
        $this->containerAttributes = $containerAttributes;
    }

    public function getName(): string
    {
        return 'container.created';
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        return [
            'containerName' => $data['containerName'],
            'containerAttributes' => $this->getContainerAttributes(),
            'domainNames' => $data['domainNames'],
        ];
    }

    public function getContainerAttributes(): array
    {
        return $this->containerAttributes;
    }
}
