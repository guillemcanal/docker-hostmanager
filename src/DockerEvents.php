<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Listener\DockerEventListener;

class DockerEvents
{
    /** @var Docker */
    private $docker;

    /** @var DockerEvents[] */
    private $listeners;

    /** @var array */
    private $options = [];

    public function __construct(Docker $docker = null)
    {
        $this->docker = $docker ?: Docker::create();
    }

    public function addListener(DockerEventListener $listener): self
    {
        $this->listeners[] = $listener;

        return $this;
    }

    public function listenSince(int $seconds): self
    {
        $this->options['since'] = (string) (\time() - $seconds);

        return $this;
    }

    public function listenUntil(int $seconds): self
    {
        $this->options['until'] = (string) (\time() + $seconds);

        return $this;
    }

    public function listen(): void
    {
        /** @var EventStream $events */
        $events = $this->docker->systemEvents($this->options);
        $events->onFrame(
            function (EventsGetResponse200 $event) {
                foreach ($this->listeners as $listener) {
                    if ($listener->support($event)) {
                        $listener->handle($event);
                    }
                }
            }
        );
        $events->wait();
    }
}
