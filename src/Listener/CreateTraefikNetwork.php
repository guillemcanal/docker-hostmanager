<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\Network;
use Docker\API\Model\NetworksCreatePostBody;
use Docker\Docker;
use ElevenLabs\DockerHostManager\Event\ApplicationStarted;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;

class CreateTraefikNetwork implements EventListener
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
            ApplicationStarted::class,
            function (): void {
                $this->createTraefikNetworkIfAbsent();
            }
        );
    }

    private function createTraefikNetworkIfAbsent(): void
    {
        $matched = \array_filter(
            $this->docker->networkList(),
            function (Network $network) {
                return self::TRAEFIK_NETWORK_NAME === $network->getName();
            }
        );

        if (empty($matched)) {
            $this->docker->networkCreate((new NetworksCreatePostBody())->setName(self::TRAEFIK_NETWORK_NAME));
        }
    }
}
