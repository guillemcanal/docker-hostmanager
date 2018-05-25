<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;

class VerifyManagedHosts
{
    /** @var HostsFileManager */
    private $hostsFileManager;
    /** @var array|DomainNameExtractor[] */
    private $domainNameExtractors = [];
    /** @var Docker */
    private $docker;

    public function __construct(
        HostsFileManager $hostsFileManager,
        Docker $docker,
        DomainNameExtractor ...$domainNameExtractors
    ) {
        $this->hostsFileManager = $hostsFileManager;
        $this->docker = $docker;
        $this->domainNameExtractors = $domainNameExtractors;
    }

    public function verify(): void
    {
        $containerList = $this->docker->containerList();
        $this->hostsFileManager->clear();
        /** @var ContainerSummaryItem $containerSummaryItem */
        foreach ($containerList as $containerSummaryItem) {
            $containerLabels = $containerSummaryItem->getLabels();
            if ($containerLabels !== null) {
                $this->extractDomainNames($containerLabels);
            }
        }
        $this->hostsFileManager->updateHostsFile();
    }

    private function extractDomainNames(\ArrayObject $containerLabels): void
    {
        foreach ($this->domainNameExtractors as $hostsExtractor) {
            if ($hostsExtractor->provideDomainNames($containerLabels)) {
                $hostnames = $hostsExtractor->getDomainNames($containerLabels);
                $this->addHostnames($hostnames);
            }
        }
    }

    private function addHostnames(array $hostnames): void
    {
        foreach ($hostnames as $hostname) {
            $this->hostsFileManager->addDomainName($hostname);
        }
    }
}
