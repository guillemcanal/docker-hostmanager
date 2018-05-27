<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Cert;

use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

/**
 * Provide a certificate and its private key.
 */
class CertificateBundle
{
    /** @var Certificate */
    private $certificate;

    /** @var PrivateKeyInfo */
    private $privateKeyInfo;

    public function __construct(Certificate $certificate, PrivateKeyInfo $privateKeyInfo)
    {
        $this->certificate = $certificate;
        $this->privateKeyInfo = $privateKeyInfo;
    }

    public function getCertificate(): Certificate
    {
        return $this->certificate;
    }

    public function getPrivateKeyInfo(): PrivateKeyInfo
    {
        return $this->privateKeyInfo;
    }
}
