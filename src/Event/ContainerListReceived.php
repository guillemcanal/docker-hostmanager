<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\Container;
use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class ContainerListReceived implements Event
{
    private $containerList;

    public function __construct(Container ...$containerList)
    {
        $this->containerList = $containerList;
    }

    public function getName(): string
    {
        return 'container.list.received';
    }

    public function toArray(): array
    {
        return ['containerList' => \array_map(
            function (Container $container) {
                return $container->getName();
            },
            $this->getContainerList()
        )];
    }

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_INTERNAL);
    }

    /**
     * @return array|DomainName[]
     */
    public function getContainerList(): array
    {
        return $this->containerList;
    }
}
