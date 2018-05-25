<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;

class UpdateHostsFile implements DockerEvent
{
    private $hostsFileManager;
    private $hostsExtractors;

    public function __construct(HostsFileManager $hostsFileManager, DomainNameExtractor ...$hostsExtractors)
    {
        $this->hostsFileManager = $hostsFileManager;
        $this->hostsExtractors  = $hostsExtractors;
    }

    public function handle(EventsGetResponse200 $event): void
    {
        if (($eventActor = $event->getActor()) === null) {
            return;
        }
        if (($containerAttributes = $eventActor->getAttributes()) === null) {
            return;
        }

        foreach ($this->hostsExtractors as $hostsExtractor) {
            if ($hostsExtractor->provideDomainNames($containerAttributes)) {
                $hosts = $hostsExtractor->getDomainNames($containerAttributes);
                if ($event->getAction() === 'create') {
                    $this->addDomainNames($hosts);
                }
                if ($event->getAction() === 'destroy') {
                    $this->removeDomainNames($hosts);
                }
                $this->hostsFileManager->updateHostsFile();
            }
        }
    }

    public function support(EventsGetResponse200 $event): bool
    {
        return $event->getType() === 'container'
            && \in_array($event->getAction(), ['create', 'destroy'], true);
    }

    private function addDomainNames(array $hosts): void
    {
        foreach ($hosts as $host) {
            $this->hostsFileManager->addDomainName($host);
        }
    }

    private function removeDomainNames(array $hosts): void
    {
        foreach ($hosts as $host) {
            $this->hostsFileManager->removeDomainName($host);
        }
    }
}
