<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class CreateSignedCertificate implements EventListener, EventProducer
{
    private $certificateGenerator;
    private $directory;
    private $producedEvents = [];

    public function __construct(
        CertificateGenerator $certificateGenerator,
        Directory $directory
    ) {
        $this->certificateGenerator = $certificateGenerator;
        $this->directory = $directory;
    }

    private function handle(DomainNamesAdded $event): void
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
            DomainNamesAdded::class,
            function (DomainNamesAdded $event): void {
                $this->handle($event);
            }
        );
    }

    public function producedEvents(): array
    {
        $events = $this->producedEvents;
        $this->producedEvents = [];

        return $events;
    }

    private function produceEvent(SignedCertificateCreated $event): void
    {
        $this->producedEvents[] = $event;
    }
}
