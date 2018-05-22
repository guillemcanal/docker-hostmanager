<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Crypto;

final class PrivateKey
{
    private $pem;

    public function __construct(string $pem)
    {
        $this->pem = $pem;
    }

    public function toPem(): string
    {
        return $this->pem;
    }
}