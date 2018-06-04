<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class CreateSignedCertificate implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $certificateGenerator;
    private $directory;

    public function __construct(
        CertificateGenerator $certificateGenerator,
        Directory $directory
    ) {
        $this->certificateGenerator = $certificateGenerator;
        $this->directory = $directory;
    }

    private function handle(ContainerCreated $event): void
    {
        $containerName = $event->getContainerName();
        $domainNames = $event->getDomainNames();

        $certificateBundle = $this->certificateGenerator->generate($domainNames);

        $certFile = $this->directory->file('certs/'.$containerName.'.crt');
        $certFile->put((string) $certificateBundle->getCertificate()->toPEM());

        $keyFile = $this->directory->file('keys/'.$containerName.'.key');
        $keyFile->put((string) $certificateBundle->getPrivateKeyInfo()->toPEM());

        $this->produceEvent(new SignedCertificateCreated($containerName, $domainNames, $certFile->uri(), $keyFile->uri()));
    }

    /**
     * {@inheritdoc}
     */
    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            ContainerCreated::class,
            function (ContainerCreated $event): void {
                $this->handle($event);
            }
        );
    }
}
