<?php

namespace ElevenLabs\DockerHostManager\Cert;

use PHPUnit\Framework\TestCase;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class CertificateBundleTest extends TestCase
{
    /** @test */
    public function it bundle a certificate and a private key()
    {
        $certificateMock    = $this->prophesize(Certificate::class);
        $privateKeyInfoMock = $this->prophesize(PrivateKeyInfo::class);

        $certificateBundle = new CertificateBundle(
            $certificateMock->reveal(),
            $privateKeyInfoMock->reveal()
        );

        assertThat($certificateBundle->getCertificate(), isInstanceOf(Certificate::class));
        assertThat($certificateBundle->getPrivateKeyInfo(), isInstanceOf(PrivateKeyInfo::class));
    }
}