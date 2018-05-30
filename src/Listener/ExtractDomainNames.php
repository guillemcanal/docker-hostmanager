<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;

class ExtractDomainNames implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $extractors;

    public function __construct(DomainNameExtractor ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DockerEventReceived::class,
            function (DockerEventReceived $event): void {
                $dockerEvent = $event->getEvent();
                if ($this->support($dockerEvent)) {
                    $this->handle($dockerEvent);
                }
            }
        );
    }

    private function support(EventsGetResponse200 $event)
    {
        return 'container' === $event->getType()
            && \in_array($event->getAction(), ['create', 'destroy'], true)
            && null !== $event->getActor()
            && null !== $event->getActor()->getAttributes();
    }

    private function handle(EventsGetResponse200 $event): void
    {
        $containerAttributes = \iterator_to_array($event->getActor()->getAttributes());
        $containerName = $this->getContainerName($containerAttributes);
        $domainNames = $this->extractDomainNamesFromContainerAttributes($containerAttributes);

        if (!empty($domainNames) && 'create' === $event->getAction()) {
            $this->produceEvent(new DomainNamesAdded($containerName, $domainNames, $containerAttributes));
        }
        if (!empty($domainNames) && 'destroy' === $event->getAction()) {
            $this->produceEvent(new DomainNamesRemoved($containerName, $domainNames));
        }
    }

    private function getContainerName(array $containerAttributes): string
    {
        return $containerAttributes['name'];
    }

    private function extractDomainNamesFromContainerAttributes(array $containerAttributes): array
    {
        $domainNames = [];
        foreach ($this->extractors as $extractor) {
            if ($extractor->provideDomainNames($containerAttributes)) {
                \array_push($domainNames, ...$extractor->getDomainNames($containerAttributes));
            }
        }

        return $domainNames;
    }
}
