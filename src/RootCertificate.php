<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\Cert\Subject;
use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
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
use X509\Certificate\Certificate;
use X509\Certificate\Extension\BasicConstraintsExtension;
use X509\Certificate\Extension\KeyUsageExtension;
use X509\Certificate\Extension\SubjectKeyIdentifierExtension;
use X509\Certificate\TBSCertificate;
use X509\Certificate\Validity;

class RootCertificate
{
    private $directory;
    private $rsaKeyGenerator;
    private $subject;

    public function __construct(
        Directory $directory,
        RsaKeyGenerator $rsaKeyGenerator,
        Subject $subject
    ) {
        $this->directory = $directory;
        $this->rsaKeyGenerator = $rsaKeyGenerator;
        $this->subject = $subject;
    }

    public function get(): CertificateBundle
    {
        $rootCAKey = $this->directory->file('root-ca.key');
        $rootCAFile = $this->directory->file('root-ca.crt');
        if ($rootCAFile->exists() && $rootCAKey->exists()) {
            return $this->createFromFiles($rootCAFile, $rootCAKey);
        }

        $bundle = $this->generateCertificateBundle();

        $rootCAFile->put((string) $bundle->getCertificate()->toPEM());
        $rootCAKey->put((string) $bundle->getPrivateKeyInfo()->toPEM());

        return $bundle;
    }

    private function createFromFiles(File $rootCAFile, File $rootCAKey): CertificateBundle
    {
        return new CertificateBundle(
            Certificate::fromPEM(PEM::fromString($rootCAFile->read())),
            PrivateKeyInfo::fromPEM(PEM::fromString($rootCAKey->read()))
        );
    }

    private function generateCertificateBundle(): CertificateBundle
    {
        $privateKeyInfo = $this->getPrivateKeyInfo();
        $tbsCertificate = $this->getTbsCertificate($this->getSubject(), $privateKeyInfo->publicKeyInfo());
        $signatureAlgorithmIdentifier = $this->getSignatureAlgorithmIdentifier($privateKeyInfo);

        $rootCA = $tbsCertificate->sign($signatureAlgorithmIdentifier, $privateKeyInfo);

        return new CertificateBundle($rootCA, $privateKeyInfo);
    }

    /**
     * @return Name
     */
    private function getSubject(): Name
    {
        return new Name(
            RDN::fromAttributeValues(new OrganizationNameValue($this->subject->getOrganizationName())),
            RDN::fromAttributeValues(new CommonNameValue($this->subject->getCommonName())),
            RDN::fromAttributeValues(new CountryNameValue($this->subject->getCountryName())),
            RDN::fromAttributeValues(new StateOrProvinceNameValue($this->subject->getStateOrProvinceName())),
            RDN::fromAttributeValues(new LocalityNameValue($this->subject->getLocalityName()))
        );
    }

    private function getTbsCertificate(Name $name, PublicKeyInfo $publicKeyInfo): TBSCertificate
    {
        $tbsCert = new TBSCertificate(
            $name,
            $publicKeyInfo,
            $name,
            Validity::fromStrings('now', 'now + 10 years')
        );

        return $tbsCert
            ->withRandomSerialNumber()
            ->withAdditionalExtensions(
                new BasicConstraintsExtension(true, true),
                new SubjectKeyIdentifierExtension(false, $publicKeyInfo->keyIdentifier()),
                new KeyUsageExtension(
                    true,
                    KeyUsageExtension::DIGITAL_SIGNATURE | KeyUsageExtension::KEY_CERT_SIGN
                )
            );
    }

    private function getPrivateKeyInfo(): PrivateKeyInfo
    {
        return PrivateKeyInfo::fromPEM(PEM::fromString($this->rsaKeyGenerator->generate()));
    }

    private function getSignatureAlgorithmIdentifier(PrivateKeyInfo $privateKeyInfo): SignatureAlgorithmIdentifier
    {
        return SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
            $privateKeyInfo->algorithmIdentifier(),
            new SHA256AlgorithmIdentifier()
        );
    }
}
