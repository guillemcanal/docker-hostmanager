<?php

// Create a root certificate

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
use X509\Certificate\TBSCertificate;
use X509\Certificate\Validity;
use X509\Certificate\Extension\BasicConstraintsExtension;
use X509\Certificate\Extension\KeyUsageExtension;
use X509\Certificate\Extension\SubjectKeyIdentifierExtension;

require dirname(__DIR__) . '/vendor/autoload.php';

// Generate a PEM RSA private key
$pemRsa = (new RsaKeyGenerator(2048))->generateKey()->toPem();

file_put_contents('root.key', $pemRsa);

// load RSA private key from PEM
$privateKeyInfo = PrivateKeyInfo::fromPEM(PEM::fromString($pemRsa));

// extract public key from private key
$publicKeyInfo = $privateKeyInfo->publicKeyInfo();

// DN of the certification authority
$name = new Name(
    RDN::fromAttributeValues(new OrganizationNameValue('DockerStack')),
    RDN::fromAttributeValues(new CommonNameValue('DockerStack Root CA')),
    RDN::fromAttributeValues(new CountryNameValue('FR')),
    RDN::fromAttributeValues(new StateOrProvinceNameValue('Paris')),
    RDN::fromAttributeValues(new LocalityNameValue('Paris'))
);

// Validity period
$validity = Validity::fromStrings('now', 'now + 10 years');

// Create 'to be signed' certificate object with extensions
$tbsCert = new TBSCertificate($name, $publicKeyInfo, $name, $validity);
$tbsCert = $tbsCert
    ->withRandomSerialNumber()
    ->withAdditionalExtensions(
        new BasicConstraintsExtension(true, true),
        new SubjectKeyIdentifierExtension(false, $publicKeyInfo->keyIdentifier()),
        new KeyUsageExtension(
            true,
            KeyUsageExtension::DIGITAL_SIGNATURE
            | KeyUsageExtension::KEY_CERT_SIGN
        )
    );

$algo = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
    $privateKeyInfo->algorithmIdentifier(),
    new SHA256AlgorithmIdentifier()
);

$cert = $tbsCert->sign($algo, $privateKeyInfo);

file_put_contents('root.pem', (string) $cert);
