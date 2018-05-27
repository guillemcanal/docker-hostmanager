<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;

class SignedCertificateCreated implements Event
{
    private $containerName;
    private $domainNames;
    private $certificateUri;
    private $privateKeyUri;

    public function getType(): EventType
    {
        return new EventType(EventType::EVENT_STANDARD);
    }

    public function getName(): string
    {
        return 'signed.certificate.created';
    }

    public function toArray(): array
    {
        return [
            'containerName'  => $this->containerName,
            'domainNames'    => $this->domainNames,
            'certificateUri' => $this->certificateUri,
            'privateKeyUri'  => $this->privateKeyUri,
        ];
    }

    public function __construct(string $containerName, array $domainNames, string $certificateUri, string $privateKeyUri)
    {
        $this->containerName  = $containerName;
        $this->domainNames    = $domainNames;
        $this->certificateUri = $certificateUri;
        $this->privateKeyUri  = $privateKeyUri;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function getDomainNames(): array
    {
        return $this->domainNames;
    }

    public function getCertificateUri(): string
    {
        return $this->certificateUri;
    }

    public function getPrivateKeyUri(): string
    {
        return $this->privateKeyUri;
    }
}
