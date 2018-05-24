<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\HostsExtractor;

interface HostsExtractor
{
    public function hasHosts(\ArrayObject $attributes): bool;
    public function getHosts(\ArrayObject $attributes): array;
}
