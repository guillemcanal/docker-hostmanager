<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\DomainNameExtractor;

interface DomainNameExtractor
{
    public function provideDomainNames(\ArrayObject $attributes): bool;

    public function getDomainNames(\ArrayObject $attributes): array;
}
