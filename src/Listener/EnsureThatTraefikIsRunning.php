<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Exception\ImageInspectNotFoundException;
use Docker\API\Model\ContainersCreatePostBody;
use Docker\API\Model\HostConfig;
use Docker\API\Model\PortBinding;
use Docker\Docker;
use Docker\Stream\CreateImageStream;
use ElevenLabs\DockerHostManager\Event\ApplicationStarted;
use ElevenLabs\DockerHostManager\Event\EventProcessed;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

/**
 * @todo This event listener is a mess. It need to be refactored
 */
class EnsureThatTraefikIsRunning implements EventListener, EventProducer
{
    use EventProducerTrait;

    private const TRAEFIK_CONTAINER_NAME = 'docker-hostmanager-traefik';
    private const TRAEFIK_VERSION = 'v1.6.2';
    public const TRAEFIK_CONF_DIRECTORY = 'traefik';

    private $docker;
    private $directory;

    public function __construct(Docker $docker, Directory $directory)
    {
        $this->docker = $docker;
        $this->directory = $directory;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ApplicationStarted::class,
            function (): void {
                $this->check();
            }
        );
    }

    private function check(): void
    {
        $containerList = $this->docker->containerList(
            [
                'all' => true,
                'filters' => \json_encode(['name' => [self::TRAEFIK_CONTAINER_NAME]]),
            ]
        );

        if (0 === \count($containerList)) {
            $this->pullTraefikImage();
            $this->createTraefikContainer();

            return;
        }

        // @todo Check that the existing traefik container is properly configured and use the proper traefik version
        $traefikContainer = \current($containerList);
        if ('exited' === $traefikContainer->getState()) {
            $this->startTraefikContainer($traefikContainer->getId());
        }
    }

    private function createTraefikContainer(): void
    {
        // @todo Create a specific Docker network for Traefik
        $this->ensureThatTheTraefikConfDirectoryExists();

        $containerCreateBody = (new ContainersCreatePostBody())
            ->setImage('traefik:'.self::TRAEFIK_VERSION)
            ->setExposedPorts(
                new \ArrayObject(
                    [
                        '80/tcp' => new \ArrayObject(),
                        '8080/tcp' => new \ArrayObject(),
                        '443/tcp' => new \ArrayObject(),
                    ]
                )
            )
            ->setLabels(
                new \ArrayObject(
                    [
                        'traefik.enable' => 'true',
                        'traefik.backend' => 'traefik',
                        'traefik.port' => '8080',
                        'traefik.frontend.rule' => 'Host: traefik.docker',
                    ]
                )
            )
            ->setHostConfig((new HostConfig())
                ->setPortBindings(
                    new \ArrayObject(
                        [
                            '80/tcp' => [(new PortBinding())->setHostIp('0.0.0.0')->setHostPort('80')],
                            '8080/tcp' => [(new PortBinding())->setHostIp('0.0.0.0')->setHostPort('8080')],
                            '443/tcp' => [(new PortBinding())->setHostIp('0.0.0.0')->setHostPort('443')],
                        ]
                    )
                )
                ->setBinds(
                    [
                        /* @todo the docker volume name is provided by the user. find a proper solution to get the volume name  */
                        'docker-hostmanager-data:/data:rw',
                        '/var/run/docker.sock:/var/run/docker.sock:ro',
                    ]
                )
            )
            ->setCmd(
                [
                    '--defaultEntryPoints=http,https',
                    '--entryPoints=Name:http Address::80',
                    '--entryPoints=Name:https Address::443 TLS',
                    '--web',
                    '--file',
                    '--file.directory=/data/'.self::TRAEFIK_CONF_DIRECTORY,
                    '--docker',
                    '--docker.watch=true',
                    '--docker.exposedByDefault=false',
                    '--logLevel=INFO',
                ]
            );

        $containerCreatedResponse = $this->docker->containerCreate(
            $containerCreateBody,
            ['name' => self::TRAEFIK_CONTAINER_NAME]
        );

        $this->startTraefikContainer($containerCreatedResponse->getId());
        $this->produceEvent(new EventProcessed('traefik container created'));
    }

    private function startTraefikContainer(string $containerId): void
    {
        $this->docker->containerStart($containerId);
        $this->produceEvent(new EventProcessed('traefik container started'));
    }

    private function pullTraefikImage(): void
    {
        try {
            $this->docker->imageInspect('traefik:'.self::TRAEFIK_VERSION);
        } catch (ImageInspectNotFoundException $e) {
            /** @var CreateImageStream $createImageStream */
            $createImageStream = $this->docker->imageCreate(
                '',
                ['fromImage' => 'traefik', 'tag' => self::TRAEFIK_VERSION]
            );
            $createImageStream->wait();
            $this->produceEvent(new EventProcessed('pulled traefik image '.self::TRAEFIK_VERSION));
        }
    }

    private function ensureThatTheTraefikConfDirectoryExists(): void
    {
        $traefikConfDirectory = $this->directory->directory(self::TRAEFIK_CONF_DIRECTORY);
        if (!$traefikConfDirectory->exists()) {
            $traefikConfDirectory->create();
            $this->produceEvent(new EventProcessed('traefik tls config directory created'));
        }
    }
}
