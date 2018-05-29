<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\Event\TraefikTlsConfigurationRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class RemoveTraefikTlsConfiguration implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            SignedCertificateRemoved::class,
            function (SignedCertificateRemoved $event): void {
                $this->removeTlsConfiguration($event);
            }
        );
    }

    public function removeTlsConfiguration(SignedCertificateRemoved $event): void
    {
        $traefikConfDirectory = $this->directory->directory(EnsureThatTraefikIsRunning::TRAEFIK_CONF_DIRECTORY);
        $containerTlsConfigFile = $traefikConfDirectory->file($event->getContainerName().'.toml');
        if ($containerTlsConfigFile->exists()) {
            $containerTlsConfigFile->delete();
            $this->produceEvent(new TraefikTlsConfigurationRemoved($event->getContainerName()));
        }
    }
}
