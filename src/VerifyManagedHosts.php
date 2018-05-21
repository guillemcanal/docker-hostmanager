<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use ElevenLabs\DockerHostManager\HostsExtractor\HostsExtractor;

class VerifyManagedHosts
{
    private $hostsFileManager;
    private $hostsExtractor;
    private $docker;

    public function __construct(
        HostsFileManager $hostsFileManager,
        HostsExtractor $hostsExtractor,
        Docker $docker
    ) {
        $this->hostsFileManager = $hostsFileManager;
        $this->hostsExtractor = $hostsExtractor;
        $this->docker = $docker;
    }

    public function verify(): void
    {
        $containerList = $this->docker->containerList();
        $this->hostsFileManager->clear();
        /** @var ContainerSummaryItem $containerSummaryItem */
        foreach ($containerList as $containerSummaryItem) {
            $containerLabels = $containerSummaryItem->getLabels();
            if ($this->hostsExtractor->hasHosts($containerLabels)) {
                $hostnames = $this->hostsExtractor->getHosts($containerLabels);
                foreach ($hostnames as $hostname) {
                    $this->hostsFileManager->addHostname($hostname);
                }
            }
        }
        $this->hostsFileManager->updateHostsFile();
    }
}