<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\Event\DomainNamesEvent;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;

class ExtractDomainNames implements EventListener, EventProducer
{
    private $extractors;
    private $producedEvents = [];

    public function __construct(DomainNameExtractor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DockerEventReceived::class,
            function (DockerEventReceived $event) {
                $dockerEvent = $event->getEvent();
                if ($this->support($dockerEvent)) {
                    $this->handle($dockerEvent);
                }
            }
        );
    }

    public function producedEvents(): array
    {
        $events = $this->producedEvents;
        $this->producedEvents = [];

        return $events;
    }

    private function support(EventsGetResponse200 $event)
    {
        return $event->getType() === 'container'
            && \in_array($event->getAction(), ['create', 'destroy'], true)
            && $event->getActor() !== null
            && $event->getActor()->getAttributes() !== null;
    }

    private function handle(EventsGetResponse200 $event)
    {
        $containerAttributes = $event->getActor()->getAttributes();
        $containerName = $this->getContainerName($containerAttributes);
        $domainNames = $this->extractDomainNamesFromTheHostsFile($containerAttributes);

        if (!empty($domainNames) && $event->getAction() === 'create') {
            $this->produceEvent(new DomainNamesAdded($containerName, $domainNames));
        }
        if (!empty($domainNames) && $event->getAction() === 'destroy') {
            $this->produceEvent(new DomainNamesRemoved($containerName, $domainNames));
        }
    }

    private function getContainerName(\ArrayObject $containerAttributes): string
    {
        return $containerAttributes['name'];
    }

    private function extractDomainNamesFromTheHostsFile(\ArrayObject $containerAttributes): array
    {
        $dnsNames = [];
        foreach ($this->extractors as $extractor) {
            if ($extractor->provideDomainNames($containerAttributes)) {
                \array_push($dnsNames, ...$extractor->getDomainNames($containerAttributes));
            }
        }

        return $dnsNames;
    }

    private function produceEvent(DomainNamesEvent $event)
    {
        $this->producedEvents[] = $event;
    }
}