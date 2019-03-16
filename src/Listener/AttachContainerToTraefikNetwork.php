<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\NetworksIdConnectPostBody;
use Docker\Docker;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;

class AttachContainerToTraefikNetwork implements EventListener
{
    private const TRAEFIK_NETWORK_NAME = 'traefik';
    private $docker;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerCreated::class,
            function (ContainerCreated $event): void {
                $this->attach($event);
            }
        );
    }

    private function attach(ContainerCreated $event): void
    {
        $containerName = $event->getContainerName();
        if (!$this->containerAttachedToTraefikNetwork($containerName)) {
            $this->docker->networkConnect(
                'traefik',
                (new NetworksIdConnectPostBody())->setContainer($containerName)
            );
            // The container needs to be restarted so Traefik can use the traefik network interface
            $this->docker->containerRestart($containerName);
        }
    }

    private function containerAttachedToTraefikNetwork(string $containerName): bool
    {
        $network = $this->docker->networkInspect(self::TRAEFIK_NETWORK_NAME);
        $containers = $network->getContainers() ?: new \ArrayObject();
        foreach ($containers as $container) {
            if ($container->getName() === $containerName) {
                return true;
            }
        }

        return false;
    }
}
