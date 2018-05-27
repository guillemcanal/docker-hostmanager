<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class DockerEventReceived implements Event
{
    private $event;

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_INTERNAL);
    }

    public function getName(): string
    {
        return 'docker.event.received';
    }

    public function toArray(): array
    {
        return [
            'type' => $this->event->getType(),
            'action' => $this->event->getAction(),
        ];
    }

    public function __construct(EventsGetResponse200 $event)
    {
        $this->event = $event;
    }

    public function getEvent(): EventsGetResponse200
    {
        return $this->event;
    }
}
