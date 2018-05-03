<?php

namespace ElevenLabs\DockerHostManager\HostsProvider;

interface HostsProvider
{
    public function hasHosts(\ArrayObject $attributes): bool;
    public function getHosts(\ArrayObject $attributes): array;
}
