<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\CertificateGenerator as CertificateGeneratorService;

class GenerateSignedCertificate implements DockerEvent
{
    private $generator;
    private $extractors;
    private $fileFactory;

    public function __construct(
        CertificateGeneratorService $generator,
        FileFactory $fileFactory,
        DomainNameExtractor ...$extractors
    ) {
        $this->generator = $generator;
        $this->fileFactory = $fileFactory;
        $this->extractors = $extractors;
    }

    public function handle(EventsGetResponse200 $event): void
    {
        if (($eventActor = $event->getActor()) === null) {
            return;
        }
        if (($containerAttributes = $eventActor->getAttributes()) === null) {
            return;
        }
        if (($dnsNames = $this->getDnsNames($containerAttributes)) === []) {
            return;
        }

        $containerName = $this->getContainerName($containerAttributes);
        $certificateBundle = $this->generator->generate($dnsNames);

        $this->saveCertificate($containerName, $certificateBundle);

        // @todo update Treafik conf
    }

    public function support(EventsGetResponse200 $event): bool
    {
        return $event->getType() === 'container'
            && \in_array($event->getAction(), ['create', 'destroy'], true);
    }

    private function getContainerName(\ArrayObject $containerAttributes): string
    {
        return $containerAttributes['name'];
    }

    private function getDnsNames(\ArrayObject $containerAttributes): array
    {
        $dnsNames = [];
        foreach ($this->extractors as $extractor) {
            if ($extractor->provideDomainNames($containerAttributes)) {
                \array_push($dnsNames, ...$extractor->getDomainNames($containerAttributes));
            }
        }

        return $dnsNames;
    }

    /**
     * @throws \ElevenLabs\DockerHostManager\File\Exception\CouldNotWriteFile
     * @throws \ElevenLabs\DockerHostManager\File\Exception\FileDoesNotExist
     */
    private function saveCertificate(string $containerName, CertificateBundle $certificateBundle): void
    {
        $certFile = $this->fileFactory->getFile('certs/' . $containerName . '.crt');
        $certFile->put((string) $certificateBundle->getCertificate()->toPEM());

        $keyFile = $this->fileFactory->getFile('keys/' . $containerName . '.key');
        $keyFile->put((string) $certificateBundle->getPrivateKeyInfo()->toPEM());
    }
}
