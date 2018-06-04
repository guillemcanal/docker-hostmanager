<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Crypto;

use PHPUnit\Framework\TestCase;

class RsaKeyGeneratorTest extends TestCase
{
    /** @test */
    public function it_generate_a_valid_rsa_private_key()
    {
        $rsaPrivateKey  = (new RsaKeyGenerator())->generate();
        $privateKeyDetails = $this->getPrivateKeyDetails($rsaPrivateKey);

        assertThat($privateKeyDetails['bits'], equalTo(RsaKeyGenerator::DEFAULT_KEY_SIZE));
        assertThat($privateKeyDetails['type'], equalTo(OPENSSL_KEYTYPE_RSA));
    }

    /** @test */
    public function it_accept_a_key_size()
    {
        $rsaPrivateKey  = (new RsaKeyGenerator($expectedKeySize = 4096))->generate();
        $privateKeyDetails = $this->getPrivateKeyDetails($rsaPrivateKey);

        assertThat($privateKeyDetails['bits'], equalTo($expectedKeySize));
        assertThat($privateKeyDetails['type'], equalTo(OPENSSL_KEYTYPE_RSA));
    }

    /** @test */
    public function it_throw_an_expection_when_the_key_size_is_to_small()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Keys with fewer than 2048 bits are not allowed.');
        new RsaKeyGenerator(128);
    }

    private function getPrivateKeyDetails(string $pemString): array
    {
        $resource = \openssl_pkey_get_private($pemString);
        if ($resource === false) {
            $this->fail('The given PEM string is not a valid private key');
        }

        return \openssl_pkey_get_details($resource);
    }
}