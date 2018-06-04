<?php

namespace ElevenLabs\DockerHostManager\Cert;

use PHPUnit\Framework\TestCase;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class CertificateBundleTest extends TestCase
{
    /** @test */
    public function it_bundle_a_certificate_and_a_private_key()
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