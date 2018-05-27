<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\HostsFileManager;

class CleanTheHostsFile implements EventListener, EventProducer
{
    private $hostsFileManager;
    private $producedEvents = [];

    public function __construct(HostsFileManager $hostsFileManager)
    {
        $this->hostsFileManager = $hostsFileManager;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerListReceived::class,
            function (ContainerListReceived $event): void {
                $this->cleanup($event->getDomainNames());
            }
        );
    }

    public function producedEvents(): array
    {
        $events = $this->producedEvents;
        $this->producedEvents = [];

        return $events;
    }

    public function cleanup(array $containerNames): void
    {
        foreach ($this->hostsFileManager->getDomainNames() as $domainName) {
            if (!\in_array($domainName->getContainerName(), $containerNames, true)) {
                $this->produceEvent(new DomainNamesRemoved($domainName->getContainerName(), [$domainName->getName()]));
            }
        }
    }

    private function produceEvent($event): void
    {
        $this->producedEvents[] = $event;
    }
}
