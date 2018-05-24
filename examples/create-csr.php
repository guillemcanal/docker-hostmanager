<?php
// Create a CSR and use the SAN extension

use ElevenLabs\DockerHostManager\Crypto\RsaKeyGenerator;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
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
use X509\CertificationRequest\CertificationRequestInfo;
use X509\GeneralName\GeneralNames;

require dirname(__DIR__) . '/vendor/autoload.php';

// Generate a PEM RSA private key
$pemRsa = (new RsaKeyGenerator(2048))->generateKey()->toPem();

file_put_contents('csr.key', $pemRsa);

// Load EC private key from PEM
$privateKeyInfo = PrivateKeyInfo::fromPEM(PEM::fromString($pemRsa));

// extract public key from private key
$publicKeyInfo = $privateKeyInfo->publicKeyInfo();

// DN of the subject
$subject = new Name(
    RDN::fromAttributeValues(new CommonNameValue('dev.domain.com')),
    RDN::fromAttributeValues(new OrganizationNameValue('DockerStack')),
    RDN::fromAttributeValues(new CountryNameValue('FR')),
    RDN::fromAttributeValues(new StateOrProvinceNameValue('Paris')),
    RDN::fromAttributeValues(new LocalityNameValue('Paris'))
);

// Create certification request info
$cri = (new CertificationRequestInfo($subject, $publicKeyInfo))
    ->withExtensionRequest(
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
                    new X509\GeneralName\DNSName('api.domain.com'),
                    new X509\GeneralName\DNSName('doc.domain.com')
                )
            )
        )
    )
;

// Sign certificate request with private key
$algo = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
    $privateKeyInfo->algorithmIdentifier(),
    new SHA256AlgorithmIdentifier()
);

$csr = $cri->sign($algo, $privateKeyInfo);

file_put_contents('csr.pem', (string) $csr);