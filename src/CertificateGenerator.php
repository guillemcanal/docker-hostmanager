<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\SignatureAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use Sop\CryptoTypes\Asymmetric\PublicKeyInfo;
use X501\ASN1\AttributeValue\CommonNameValue;
use X501\ASN1\AttributeValue\CountryNameValue;
use X501\ASN1\AttributeValue\LocalityNameValue;
use X501\ASN1\AttributeValue\OrganizationNameValue;
use X501\ASN1\AttributeValue\StateOrProvinceNameValue;
use X501\ASN1\Name;
use X501\ASN1\RDN;
use X509\Certificate\Extension\BasicConstraintsExtension;
use X509\Certificate\Extension\KeyUsageExtension;
use X509\Certificate\Extension\SubjectAlternativeNameExtension;
use X509\Certificate\Extensions;
use X509\Certificate\TBSCertificate;
use X509\Certificate\Validity;
use X509\CertificationRequest\CertificationRequest;
use X509\CertificationRequest\CertificationRequestInfo;
use X509\GeneralName\DNSName;
use X509\GeneralName\GeneralNames;

/**
 * Generate a certificate
 */
class CertificateGenerator
{
    private $subject;
    private $rsaKeyGenerator;
    private $issuerCertificateBundle;

    public function __construct(
        Subject $subject,
        RsaKeyGenerator $rsaKeyGenerator,
        CertificateBundle $issuerCertificateBundle
    ) {
        $this->subject = $subject;
        $this->rsaKeyGenerator = $rsaKeyGenerator;
        $this->issuerCertificateBundle = $issuerCertificateBundle;
    }

    public function generate(array $dnsNames): CertificateBundle
    {
        $commonName = array_shift($dnsNames);
        $privateKeyInfo = $this->getPrivateKeyInfo();

        $certificationRequestInfo = $this->getCertificationRequestInfo(
            $dnsNames,
            $this->getSubject($commonName),
            $privateKeyInfo->publicKeyInfo()
        );

        $certificationRequest = $certificationRequestInfo->sign(
            $this->getSignatureAlgorithmIdentifier($privateKeyInfo),
            $privateKeyInfo
        );

        $tbsCertificate = $this->getTBSCertificate($certificationRequest);

        $signedCertificate = $tbsCertificate->sign(
            $this->getSignatureAlgorithmIdentifier($this->issuerCertificateBundle->getPrivateKeyInfo()),
            $this->issuerCertificateBundle->getPrivateKeyInfo()
        );

        return new CertificateBundle($signedCertificate, $privateKeyInfo);
    }

    private function getPrivateKeyInfo(): PrivateKeyInfo
    {
        return PrivateKeyInfo::fromPEM(PEM::fromString($this->rsaKeyGenerator->generate()));
    }

    private function getSubject(string $commonName): Name
    {
        return new Name(
            RDN::fromAttributeValues(new CommonNameValue($commonName)),
            RDN::fromAttributeValues(new OrganizationNameValue($this->subject->getOrganizationName())),
            RDN::fromAttributeValues(new CountryNameValue($this->subject->getCountryName())),
            RDN::fromAttributeValues(new StateOrProvinceNameValue($this->subject->getStateProvinceName())),
            RDN::fromAttributeValues(new LocalityNameValue($this->subject->getLocalityName()))
        );
    }

    private function getCertificationRequestInfo(
        array $dnsNames,
        Name $subject,
        PublicKeyInfo $publicKeyInfo
    ): CertificationRequestInfo {
        $certificateRequestInfo = new CertificationRequestInfo($subject, $publicKeyInfo);

        return $certificateRequestInfo->withExtensionRequest(
            new Extensions(
                new KeyUsageExtension(
                    true,
                    KeyUsageExtension::DIGITAL_SIGNATURE
                    | KeyUsageExtension::NON_REPUDIATION
                    | KeyUsageExtension::KEY_ENCIPHERMENT
                    | KeyUsageExtension::DATA_ENCIPHERMENT
                ),
                new BasicConstraintsExtension(true, false),
                new SubjectAlternativeNameExtension(
                    false,
                    new GeneralNames(
                        ...array_map(
                            function (string $dnsName) {
                                return new DNSName($dnsName);
                            },
                            $dnsNames
                        )
                    )
                )
            )
        );
    }

    private function getTBSCertificate(CertificationRequest $csr): TBSCertificate
    {
        return TBSCertificate::fromCSR($csr)
            ->withIssuerCertificate($this->issuerCertificateBundle->getCertificate())
            ->withRandomSerialNumber()
            ->withValidity(Validity::fromStrings('now', 'now + 10 years'))
            ->withAdditionalExtensions(
                new KeyUsageExtension(
                    true,
                    KeyUsageExtension::DIGITAL_SIGNATURE | KeyUsageExtension::KEY_ENCIPHERMENT
                ),
                new BasicConstraintsExtension(true, false)
            );
    }

    private function getSignatureAlgorithmIdentifier(PrivateKeyInfo $privateKeyInfo): SignatureAlgorithmIdentifier
    {
        return SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
            $privateKeyInfo->algorithmIdentifier(),
            new SHA256AlgorithmIdentifier()
        );
    }
}
