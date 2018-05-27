<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Docker\Docker;
use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use ElevenLabs\DockerHostManager\DockerEvents;
use ElevenLabs\DockerHostManager\EventDispatcher\EventDispatcher;
use ElevenLabs\DockerHostManager\File\LocalDirectory;
use ElevenLabs\DockerHostManager\File\LocalFile;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\DomainNameExtractor\TraefikFrontendRule;
use ElevenLabs\DockerHostManager\Listener\CleanTheHostsFile;
use ElevenLabs\DockerHostManager\Listener\CreateSignedCertificate;
use ElevenLabs\DockerHostManager\Listener\AddDomainNames;
use ElevenLabs\DockerHostManager\Listener\DeleteSignedCertificate;
use ElevenLabs\DockerHostManager\Listener\LogEvents;
use ElevenLabs\DockerHostManager\Listener\ExtractDomainNames;
use ElevenLabs\DockerHostManager\Listener\RemoveDomainNames;
use ElevenLabs\DockerHostManager\RootCertificate;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('docker-stack');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

/**
 * Hosts file manager configuration
 */
$dockerClient = Docker::create();
$hostsFileManager = new HostsFileManager(LocalFile::get('/etc/hosts'));
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

$certificatesDirectory = LocalDirectory::get('/data');

$issuerBundleCertificate = new RootCertificate(
    $certificatesDirectory,
    $rsaGenerator,
    $subject
);

$certificateGenerator = new CertificateGenerator(
    $subject,
    $rsaGenerator,
    $issuerBundleCertificate->get()
);

$eventDispatcher = new EventDispatcher(
    new LogEvents($logger),
    new CleanTheHostsFile($hostsFileManager),
    new ExtractDomainNames(...$domainNamesExtractors),
    new AddDomainNames($hostsFileManager),
    new RemoveDomainNames($hostsFileManager),
    new CreateSignedCertificate($certificateGenerator, $certificatesDirectory),
    new DeleteSignedCertificate($certificatesDirectory)
);

(new DockerEvents($dockerClient, $eventDispatcher))->listen();
