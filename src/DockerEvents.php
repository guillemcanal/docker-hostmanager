<?php
namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use ElevenLabs\DockerHostManager\Listener\DockerEventListener;

class DockerEvents
{
    /** @var Docker */
    private $docker;

    /** @var DockerEvents[] */
    private $listeners;

    public function __construct(Docker $docker = null)
    {
        $this->docker = $docker ?: Docker::create();
    }

    public function addListener(DockerEventListener $listener)
    {
        $this->listeners[] = $listener;
    }

    public function run()
    {
        $events = $this->docker->systemEvents();
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
