<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Crypto;

class RsaKeyGenerator
{
    public const DEFAULT_KEY_SIZE = 2048;

    private $bits;

    public function __construct(int $bits = self::DEFAULT_KEY_SIZE)
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

        \openssl_pkey_export($res, $privateKey);
        \openssl_pkey_free($res);

        return $privateKey;
    }
}