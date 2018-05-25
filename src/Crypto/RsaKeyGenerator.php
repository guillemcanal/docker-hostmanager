<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Crypto;

class RsaKeyGenerator
{
    private $bits;

    public function __construct(int $bits = 2048)
    {
        if ($bits < 2048) {
            throw new \UnexpectedValueException('Keys with fewer than 2048 bits are not allowed.');
        }
        $this->bits = $bits;
    }

    public function generate(): string
    {
        $res = \openssl_pkey_new(
            [
                'private_key_type' => \OPENSSL_KEYTYPE_RSA,
                'private_key_bits' => $this->bits,
            ]
        );

        $success = \openssl_pkey_export($res, $privateKey);

        \openssl_pkey_free($res);
        if (!$success) {
            throw new \RuntimeException('Key export failed!');
        }

        return $privateKey;
    }
}