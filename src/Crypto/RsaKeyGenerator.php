<?php

declare(strict_types=1);

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

    /**
     * @todo return an object with both public and private key
     */
    public function generateKey(): PrivateKey
    {
        $configFile = $defaultConfigFile = __DIR__ . '/openssl.cnf';
        $res = openssl_pkey_new(
            [
                'private_key_type' => \OPENSSL_KEYTYPE_RSA,
                'private_key_bits' => $this->bits,
                'config' => $configFile,
            ]
        );

        $success = openssl_pkey_export(
            $res,
            $privateKey,
            null,
            ['config' => $configFile]
        );

        if ($configFile !== $defaultConfigFile) {
            @unlink($configFile);
        }

        \openssl_pkey_free($res);
        if (!$success) {
            throw new \RuntimeException('Key export failed!');
        }

        return new PrivateKey($privateKey);
    }
}