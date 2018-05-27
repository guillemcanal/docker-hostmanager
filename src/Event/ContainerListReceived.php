<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class ContainerListReceived implements Event
{
    private $domainNames;

    public function __construct(string ...$names)
    {
        $this->domainNames = $names;
    }

    public function getName(): string
    {
        return 'container.list.received';
    }

    public function toArray(): array
    {
        return ['domainNames' => $this->domainNames];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_INTERNAL);
    }

    public function getDomainNames(): array
    {
        return $this->domainNames;
    }
}
