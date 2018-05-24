<?php
// Create a TBS Certificate from a CSR and an Issuer Certificate a sign it using the issuer private key

use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA512AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;
use X509\Certificate\TBSCertificate;
use X509\Certificate\Validity;
use X509\Certificate\Extension\BasicConstraintsExtension;
use X509\Certificate\Extension\KeyUsageExtension;
use X509\CertificationRequest\CertificationRequest;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load issuer certificate from PEM
$issuerCertificate = Certificate::fromPEM(
    PEM::fromFile(__DIR__ . '/root.pem')
);

// Load certification request from PEM
$csr = CertificationRequest::fromPEM(
    PEM::fromFile(__DIR__ . '/csr.pem')
);

// Verify CSR
if (!$csr->verify()) {
    echo 'Failed to verify certification request signature.\n';
    exit(1);
}

// Load CA's private key from PEM
$privateKeyInfo = PrivateKeyInfo::fromPEM(
    PEM::fromFile(__DIR__ . '/root.key')
);

// Initialize certificate from CSR and issuer's certificate
$tbsCertificate = TBSCertificate::fromCSR($csr)->withIssuerCertificate($issuerCertificate);

// Set random serial number
$tbsCertificate = $tbsCertificate->withRandomSerialNumber();

// Set validity period
$tbsCertificate = $tbsCertificate->withValidity(
    Validity::fromStrings('now', 'now + 10 years')
);

// Add extensions
$tbsCertificate = $tbsCertificate->withAdditionalExtensions(
    new KeyUsageExtension(
        true,
        KeyUsageExtension::DIGITAL_SIGNATURE
        | KeyUsageExtension::KEY_ENCIPHERMENT
    ),
    new BasicConstraintsExtension(true, false)
);

// Sign certificate with issuer's private key
$algo = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
    $privateKeyInfo->algorithmIdentifier(),
    new SHA512AlgorithmIdentifier()
);

$cert = $tbsCertificate->sign($algo, $privateKeyInfo);

file_put_contents('signed.pem', (string) $cert);
