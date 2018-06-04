<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\ContainerRemoved;
use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducer;
use ElevenLabs\DockerHostManager\EventDispatcher\EventProducerTrait;
use ElevenLabs\DockerHostManager\EventDispatcher\EventSubscription;
use ElevenLabs\DockerHostManager\File\Directory;

class DeleteSignedCertificate implements EventListener, EventProducer
{
    use EventProducerTrait;

    private $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    private function handle(ContainerRemoved $event): void
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
            ContainerRemoved::class,
            function (ContainerRemoved $event): void {
                $this->handle($event);
            }
        );
    }
}
