<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsExtractor\HostsExtractor;

class HostManagerListener implements DockerEventListener
{
    /** @var HostsFileManager */
    private $hostsFileManager;
    /** @var HostsExtractor[] */
    private $hostsExtractors = [];

    public function __construct(HostsFileManager $hostsFileManager, array $hostsExtractors)
    {
        $this->hostsFileManager = $hostsFileManager;
        foreach ($hostsExtractors as $hostsExtractor) {
            $this->addHostsExtractor($hostsExtractor);
        }
    }

    public function addHostsExtractor(HostsExtractor $hostsExtractor): void
    {
        $this->hostsExtractors[] = $hostsExtractor;
    }

    public function handle(EventsGetResponse200 $event): void
    {
        $containerAttributes = $event->getActor()->getAttributes();
        foreach ($this->hostsExtractors as $hostsExtractor) {
            if ($hostsExtractor->hasHosts($containerAttributes)) {
                $hosts = $hostsExtractor->getHosts($containerAttributes);
                if ($event->getAction() === 'create') {
                    $this->addHosts($hosts);
                }
                if ($event->getAction() === 'destroy') {
                    $this->removeHosts($hosts);
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

    private function addHosts(array $hosts): void
    {
        foreach ($hosts as $host) {
            $this->hostsFileManager->addHostname($host);
        }
    }

    private function removeHosts(array $hosts): void
    {
        foreach ($hosts as $host) {
            $this->hostsFileManager->removeHostname($host);
        }
    }
}
