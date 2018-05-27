<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

interface EventProducer
{
    public function producedEvents(): array;
}