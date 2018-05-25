<?php

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\File\LocalFile;
use PHPUnit\Framework\TestCase;
use X509\GeneralName\GeneralName;

class CertificateGeneratorTest extends TestCase
{
    private $issuerCertificateBundle;
    private $subject;
    private $rsaKeyGenerator;

    public function setUp()
    {
        $this->rsaKeyGenerator = new RsaKeyGenerator();

        $this->subject = new Subject(
            $organizationName  = 'ACME Inc.',
            $commonName        = 'ACME Root CA',
            $countryName       = 'FR',
            $stateProvinceName = 'Paris',
            $localityName      = 'Paris'
        );

        $this->issuerCertificateBundle = (
            new RootCertificate(
                new FileFactory(LocalFile::class, __DIR__ . '/Fixtures/root-ca'),
                $this->rsaKeyGenerator,
                $this->subject
            )
        )->get();
    }

    /** @test */
    public function it can generate a valid signed certificate()
    {
        $certificateGenerator = new CertificateGenerator(
            $this->subject,
            $this->rsaKeyGenerator,
            $this->issuerCertificateBundle
        );

        $signedCertificateBundle = $certificateGenerator->generate(['foo.domain.fr', 'bar.domain.fr']);

        assertTrue(
            $signedCertificateBundle->getCertificate()->verify(
                $this->issuerCertificateBundle->getPrivateKeyInfo()->publicKeyInfo()
            )
        );
    }

    /** @test */
    public function it use the first given dns name as a common name()
    {
        $certificateGenerator = new CertificateGenerator(
            $this->subject,
            $this->rsaKeyGenerator,
            $this->issuerCertificateBundle
        );

        $signedCertificateBundle = $certificateGenerator->generate(['foo.domain.fr', 'bar.domain.fr']);

        $attributeValue = $signedCertificateBundle
            ->getCertificate()
            ->tbsCertificate()
            ->subject()
            ->firstValueOf('commonName');

        assertThat($attributeValue->stringValue(), equalTo('foo.domain.fr'));
    }

    /** @test */
    public function it use the given dns names as subject alternative names except from the first()
    {
        $certificateGenerator = new CertificateGenerator(
            $this->subject,
            $this->rsaKeyGenerator,
            $this->issuerCertificateBundle
        );

        $signedCertificateBundle = $certificateGenerator->generate(['foo.domain.fr', 'bar.domain.fr', 'baz.domain.fr']);

        $subjectAlternativeNames = $signedCertificateBundle
            ->getCertificate()
            ->tbsCertificate()
            ->extensions()
            ->subjectAlternativeName()
            ->names();

        assertThat($subjectAlternativeNames->count(), equalTo(2));

        assertThat(
            $subjectAlternativeNames->allOf(GeneralName::TAG_DNS_NAME),
            equalTo(['bar.domain.fr', 'baz.domain.fr'])
        );
    }
}