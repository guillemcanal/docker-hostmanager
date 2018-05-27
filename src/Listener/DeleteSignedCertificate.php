<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class DeleteSignedCertificate implements EventListener, EventProducer
{
    private $directory;
    private $producedEvents = [];

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    private function handle(DomainNamesRemoved $event): void
    {
        $containerName = $event->getContainerName();
        $certFile = $this->directory->file('certs/'.$containerName.'.crt');
        if ($certFile->exists()) {
            $certFile->delete();
        }
        $keyFile = $this->directory->file('keys/'.$containerName.'.key');
        if ($keyFile->exists()) {
            $keyFile->delete();
        }

        $this->produceEvent(new SignedCertificateRemoved($containerName));
    }

    /**
     * {@inheritdoc}
     */
    public function subscription(): EventSubscription
    {
        return new EventSubscription(
            DomainNamesRemoved::class,
            function (DomainNamesRemoved $event): void {
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

    private function produceEvent(SignedCertificateRemoved $event): void
    {
        $this->producedEvents[] = $event;
    }
}
