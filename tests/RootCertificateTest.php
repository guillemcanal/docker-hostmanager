<?php

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\File\LocalDirectory;
use ElevenLabs\DockerHostManager\File\LocalFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class RootCertificateTest extends TestCase
{
    private $rootDirectory;

    public function setUp(): void
    {
        $this->rootDirectory = vfsStream::setup('data', 0755);
    }

    /** @test */
    public function it_can_create_a_valid_root_certificate()
    {
        $rootCertificate = new RootCertificate(
            new LocalDirectory($this->rootDirectory->url()),
            new RsaKeyGenerator(),
            new Subject(
                $organizationName    = 'ACME Inc.',
                $commonName          = 'ACME Root CA',
                $countryName         = 'FR',
                $stateOrProvinceName = 'Paris',
                $localityName        = 'Paris'
            )
        );

        $certificateBundle = $rootCertificate->get();
        $rootCA = $certificateBundle->getCertificate();
        $rootCAKey = $certificateBundle->getPrivateKeyInfo();

        assertThat($rootCA->verify($rootCAKey->publicKeyInfo()), equalTo(true));

        $certificateSubject = $rootCA->tbsCertificate()->subject();

        assertThat($certificateSubject->firstValueOf('organizationName')->stringValue(), equalTo($organizationName));
        assertThat($certificateSubject->firstValueOf('commonName')->stringValue(), equalTo($commonName));
        assertThat($certificateSubject->firstValueOf('countryName')->stringValue(), equalTo($countryName));
        assertThat($certificateSubject->firstValueOf('stateOrProvinceName')->stringValue(), equalTo($stateOrProvinceName));
        assertThat($certificateSubject->firstValueOf('localityName')->stringValue(), equalTo($localityName));
    }

    /** @test */
    public function it_save_the_root_certificate_and_its_key_on_the_filesystem()
    {
        $rootCertificate = new RootCertificate(
            new LocalDirectory($this->rootDirectory->url()),
            new RsaKeyGenerator(),
            new Subject(
                $organizationName    = 'ACME Inc.',
                $commonName          = 'ACME Root CA',
                $countryName         = 'FR',
                $stateOrProvinceName = 'Paris',
                $localityName        = 'Paris'
            )
        );

        $rootCertificate->get();

        assertTrue($this->rootDirectory->hasChild('root-ca.crt'));
        assertTrue($this->rootDirectory->hasChild('root-ca.key'));
    }

    /** @test */
    public function it_can_load_the_root_certificate_bundle_from_the_filesystem()
    {
        $fixturesDirectory = __DIR__ . '/Fixtures/root-ca';

        $rootCertificate = new RootCertificate(
            new LocalDirectory($fixturesDirectory),
            new RsaKeyGenerator(),
            new Subject(
                $organizationName    = 'ACME Inc.',
                $commonName          = 'ACME Root CA',
                $countryName         = 'FR',
                $stateOrProvinceName = 'Paris',
                $localityName        = 'Paris'
            )
        );

        $certificateBundle = $rootCertificate->get();
        $rootCA = $certificateBundle->getCertificate();
        $rootCAKey = $certificateBundle->getPrivateKeyInfo();

        assertStringEqualsFile($fixturesDirectory . '/root-ca.crt', (string) $rootCA->toPEM());
        assertStringEqualsFile($fixturesDirectory . '/root-ca.key', (string) $rootCAKey->toPEM());
    }
}