<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;

interface DockerEventListener
{
    public function handle(EventsGetResponse200 $event): void;
    public function support(EventsGetResponse200 $event): bool;
}