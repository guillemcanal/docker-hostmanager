<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use ElevenLabs\DockerHostManager\DockerEvents;
use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\File\LocalFile;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\DomainNameExtractor\TraefikFrontendRule;
use ElevenLabs\DockerHostManager\Listener\GenerateSignedCertificate;
use ElevenLabs\DockerHostManager\Listener\UpdateHostsFile;
use ElevenLabs\DockerHostManager\RootCertificate;
use ElevenLabs\DockerHostManager\VerifyManagedHosts;

/**
 * Hosts file manager configuration
 */
$docker = \Docker\Docker::create();
$hostsFileManager = new HostsFileManager(new LocalFile('file:///etc/hosts'));
$domainNamesExtractors = [new TraefikFrontendRule()];

/**
 * Certificate generation configuration
 */
$rsaGenerator = new RsaKeyGenerator();

$subject = new Subject(
    $organizationName  = 'ACME Inc.',
    $commonName        = 'ACME Root CA',
    $countryName       = 'FR',
    $stateProvinceName = 'Paris',
    $localityName      = 'Paris'
);

$certificateFileFactory = new FileFactory(LocalFile::class, 'file:///data');

$issuerBundleCertificate = new RootCertificate(
    $certificateFileFactory,
    $rsaGenerator,
    $subject
);

$certificateGenerator = new CertificateGenerator(
    $subject,
    $rsaGenerator,
    $issuerBundleCertificate->get()
);

/**
 * Verify that the hosts file is in sync according to the Docker containers created on this machine
 */
$verifier = new VerifyManagedHosts($hostsFileManager, $docker, ...$domainNamesExtractors);
$verifier->verify();

/**
 * Start listening for Docker events since 5 seconds until 200 seconds
 *
 * FIXME:
 * It appear that the extraction of domain names from docker containers labels is done twice.
 * For now, it's not a huge issue since parsing domain names in docker containers labels is quite cheap.
 *
 * But, in a near future,
 * I'll create a DomainNamesExtractorListener that will emit an ExtractedDomainNames event.
 * The UpdateHostsFile and GenerateSignedCertificate listeners will no longer be considered as docker event listeners
 * but rather as standard event listeners.
 *
 * A very simple event dispatcher will be introduced in the project to allow this.
 *
 * Moreover, once a signed certificate gets generated, we will need to emit a SignedCertificateCreated event.
 * This event will be used to update the TOML configuration of our HTTP reverse proxy (Traefik). IN fact, we
 * need to tell Traefik where it can find our certificates so websites running locally can be accessed using HTTPS.
 *
 * Since Traefik v1.5.3, the file based configuration is hot-reloaded to take into account newly added
 * TLS certificates. @see https://github.com/containous/traefik/pull/2233
 */
(new DockerEvents($docker))
    ->addListener(
        new UpdateHostsFile(
            $hostsFileManager,
            ...$domainNamesExtractors
        )
    )
    ->addListener(
        new GenerateSignedCertificate(
            $certificateGenerator,
            $certificateFileFactory,
            ...$domainNamesExtractors
        )
    )
    ->listenSince(5)
    ->listenUntil(200)
    ->listen();
