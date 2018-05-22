<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use ElevenLabs\DockerHostManager\HostsExtractor\HostsExtractor;

class VerifyManagedHosts
{
    /** @var HostsFileManager */
    private $hostsFileManager;
    /** @var array|HostsExtractor[] */
    private $hostsExtractors = [];
    /** @var Docker */
    private $docker;

    public function __construct(
        HostsFileManager $hostsFileManager,
        array $hostsExtractors,
        Docker $docker
    ) {
        $this->hostsFileManager = $hostsFileManager;
        $this->docker = $docker;
        foreach ($hostsExtractors as $hostsExtractor) {
            $this->addHostsExtractor($hostsExtractor);
        }
    }

    public function verify(): void
    {
        $containerList = $this->docker->containerList();
        $this->hostsFileManager->clear();
        /** @var ContainerSummaryItem $containerSummaryItem */
        foreach ($containerList as $containerSummaryItem) {
            $containerLabels = $containerSummaryItem->getLabels();
            if ($containerLabels !== null) {
                $this->extractHosts($containerLabels);
            }
        }
        $this->hostsFileManager->updateHostsFile();
    }

    private function addHostsExtractor(HostsExtractor $hostsExtractor)
    {
        $this->hostsExtractors[] = $hostsExtractor;
    }

    private function extractHosts(\ArrayObject $containerLabels): void
    {
        foreach ($this->hostsExtractors as $hostsExtractor) {
            if ($hostsExtractor->hasHosts($containerLabels)) {
                $hostnames = $hostsExtractor->getHosts($containerLabels);
                $this->addHostnames($hostnames);
            }
        }
    }

    private function addHostnames(array $hostnames): void
    {
        foreach ($hostnames as $hostname) {
            $this->hostsFileManager->addHostname($hostname);
        }
    }
}
