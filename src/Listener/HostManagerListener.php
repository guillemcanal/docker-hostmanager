<?php declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsProvider\HostsProvider;

class HostManagerListener implements DockerEventListener
{
    /** @var HostsFileManager */
    private $hostsFileManager;
    /** @var HostsProvider */
    private $hostsProvider;

    public function __construct(HostsFileManager $hostsFileManager, HostsProvider $hostsProvider)
    {
        $this->hostsFileManager = $hostsFileManager;
        $this->hostsProvider    = $hostsProvider;
    }

    public function handle(EventsGetResponse200 $event): void
    {
        $containerAttributes = $event->getActor()->getAttributes();
        if ($this->hostsProvider->hasHosts($containerAttributes)) {
            $hosts = $this->hostsProvider->getHosts($containerAttributes);
            if ($event->getAction() === 'create') {
                $this->addHosts($hosts);
            }
            if ($event->getAction() === 'destroy') {
                $this->removeHosts($hosts);
            }
            $this->hostsFileManager->updateHostsFile();
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
