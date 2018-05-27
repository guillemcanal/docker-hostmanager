<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\ErrorReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\HostsFileManager;

class AddDomainNames implements EventListener, EventProducer
{
    private $hostsFileManager;
    private $producedEvents = [];

    public function __construct(HostsFileManager $hostsFileManager)
    {
        $this->hostsFileManager = $hostsFileManager;
    }

    public function handle(DomainNamesAdded $event): void
    {
        foreach ($event->getDomainNames() as $domainNameString) {
            $domainName = new DomainName($domainNameString, $event->getContainerName());
            try {
                $this->hostsFileManager->addDomainName($domainName);
            } catch (\Exception $e) {
                $this->produceEvent(
                    new ErrorReceived(
                        \sprintf(
                            'Unable to add domain name %s for container %s',
                            $domainName->getName(),
                            $domainName->getContainerName()
                        ),
                        $e
                    )
                );
            }
        }
        $this->hostsFileManager->updateHostsFile();
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DomainNamesAdded::class,
            function (DomainNamesAdded $event): void {
                $this->handle($event);
            }
        );
    }

    public function producedEvents(): array
    {
        $events = $this->producedEvents;
        $this->producedEvents = [];

        return $events;
    }

    private function produceEvent($event): void
    {
        $this->producedEvents[] = $event;
    }
}
