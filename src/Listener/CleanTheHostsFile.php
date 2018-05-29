<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Container;
use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\HostsFileManager;

class CleanTheHostsFile implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $hostsFileManager;
    private $domainNameExtractors;

    public function __construct(HostsFileManager $hostsFileManager, DomainNameExtractor ...$domainNameExtractors)
    {
        $this->hostsFileManager = $hostsFileManager;
        $this->domainNameExtractors = $domainNameExtractors;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerListReceived::class,
            function (ContainerListReceived $event): void {
                $this->cleanup($event->getContainerList());
            }
        );
    }

    /**
     * @param array|Container[] $containerList
     */
    public function cleanup(array $containerList): void
    {
        $containerNames = \array_map(
            function (Container $container) {
                return $container->getName();
            },
            $containerList
        );

        foreach ($this->hostsFileManager->getDomainNames() as $container) {
            if (!\in_array($container->getContainerName(), $containerNames, true)) {
                $this->produceEvent(new DomainNamesRemoved($container->getContainerName(), [$container->getName()]));
            }
        }

        foreach ($containerList as $container) {
            $domainNames = $this->extractDomainNames($container->getLabels());
            $this->addDomainNamesIfAbsentFromTheHostsFile($domainNames, $container);
        }
    }

    private function extractDomainNames(\ArrayObject $containerAttributes): array
    {
        $domainNames = [];
        foreach ($this->domainNameExtractors as $domainNameExtractor) {
            if ($domainNameExtractor->provideDomainNames($containerAttributes)) {
                \array_push($domainNames, ...$domainNameExtractor->getDomainNames($containerAttributes));
            }
        }

        return $domainNames;
    }

    /**
     * @todo Duplicated from ExtractDomainNames. Need refactoring
     */
    private function addDomainNamesIfAbsentFromTheHostsFile(array $domainNames, Container $container): void
    {
        foreach ($domainNames as $domainName) {
            $domainName = new DomainName($container->getName(), $domainName);
            if (!$this->hostsFileManager->hasDomainName($domainName)) {
                $this->produceEvent(new DomainNamesAdded($container->getName(), $domainNames));
                break;
            }
        }
    }
}
