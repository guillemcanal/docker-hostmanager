<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EndpointSettings;
use Docker\API\Model\NetworksIdConnectPostBody;
use Docker\API\Model\NetworksIdDisconnectPostBody;
use Docker\Docker;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\ContainerEvent;
use ElevenLabs\DockerHostManager\Event\EventProcessed;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;

class AddDomainAliasesToTraefikNetwork implements EventListener, EventProducer
{
    private $docker;
    private $domainExtractor;
    private $previousDomainAliases = [];

    use EventProducerTrait;

    public function __construct(Docker $docker, DomainNameExtractor $domainExtractor)
    {
        $this->docker = $docker;
        $this->domainExtractor = $domainExtractor;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerEvent::class,
            function (): void {
                $this->addDomainAliasesToTraefik();
            }
        );
    }

    private function addDomainAliasesToTraefik(): void
    {
        $domainAliases = $this->getContainersDomains();

        if (
            ($this->previousDomainAliases === [] && !empty($domainAliases)) ||
            \array_diff($domainAliases, $this->previousDomainAliases) !== []
        ) {
            $this->docker->networkDisconnect(
                CreateTraefikNetwork::TRAEFIK_NETWORK_NAME,
                (new NetworksIdDisconnectPostBody())
                    ->setContainer(EnsureThatTraefikIsRunning::TRAEFIK_CONTAINER_NAME)
            );
            $this->docker->networkConnect(
                CreateTraefikNetwork::TRAEFIK_NETWORK_NAME,
                (new NetworksIdConnectPostBody())
                    ->setEndpointConfig((new EndpointSettings())->setAliases($domainAliases))
                    ->setContainer(EnsureThatTraefikIsRunning::TRAEFIK_CONTAINER_NAME)
            );
            $this->previousDomainAliases = $domainAliases;

            $this->produceEvent(
                new EventProcessed(
                    \sprintf(
                        'added aliases %s to the traefik network',
                        \implode(', ', $domainAliases)
                    )
                )
            );
        }
    }

    private function getContainersDomains(): array
    {
        $domains = [];
        foreach ($this->docker->containerList() as $item) {
            $containerLabels = ($item->getLabels() ?: new \ArrayObject())->getArrayCopy();
            if ($this->domainExtractor->provideDomainNames($containerLabels)) {
                \array_push($domains, ...$this->domainExtractor->getDomainNames($containerLabels));
            }
        }

        return \array_unique($domains);
    }
}
