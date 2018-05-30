<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\DomainNameExtractor;

interface DomainNameExtractor
{
    public function provideDomainNames(array $containerAttributes): bool;

    public function getDomainNames(array $containerAttributes): array;
}
