<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\EventDispatcher;

interface EventListener
{
    /**
     * Return an event subscription.
     */
    public function subscription(): EventSubscription;
}
