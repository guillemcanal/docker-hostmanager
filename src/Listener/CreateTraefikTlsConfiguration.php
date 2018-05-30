<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\Event\TraefikTlsConfigurationCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class CreateTraefikTlsConfiguration implements EventListener, EventProducer
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
            SignedCertificateCreated::class,
            function (SignedCertificateCreated $event): void {
                $this->createTlsConfiguration($event);
            }
        );
    }

    public function createTlsConfiguration(SignedCertificateCreated $event): void
    {
        $certFilePath = $this->directory->file('certs/'.$event->getContainerName().'.crt')->path();
        $keyFilePath = $this->directory->file('keys/'.$event->getContainerName().'.key')->path();
        $tomlConfig = <<<TOML
[[tls]]
  entryPoints = ["https"]
  [tls.certificate]
    certFile = "$certFilePath"
    keyFile = "$keyFilePath"

TOML;

        $traefikConfDirectory = $this->directory->directory(EnsureThatTraefikIsRunning::TRAEFIK_CONF_DIRECTORY);
        $containerTlsConfigFile = $traefikConfDirectory->file($event->getContainerName().'.tls.toml');
        $containerTlsConfigFile->put($tomlConfig);

        $this->produceEvent(new TraefikTlsConfigurationCreated($event->getContainerName()));
    }
}
