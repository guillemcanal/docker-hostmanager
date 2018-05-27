<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventDispatcher;

class DockerEvents
{
    /** @var Docker */
    private $docker;

    /** @var EventDispatcher */
    private $dispatcher;

    public function __construct(Docker $docker, EventDispatcher $dispatcher)
    {
        $this->docker = $docker;
        $this->dispatcher = $dispatcher;
    }

    public function listen(): void
    {
        $this->listContainerNames();
        /** @var EventStream $events */
        $events = $this->docker->systemEvents();
        $events->onFrame(
            function ($event) {
                if (\is_object($event) && $event instanceof EventsGetResponse200) {
                    $this->dispatcher->dispatch(new DockerEventReceived($event));
                }
            }
        );
        $events->wait();
    }

    private function listContainerNames(): void
    {
        $names = array_map(
            function (ContainerSummaryItem $item) {
                return ltrim(current($item->getNames()), '/');
            },
            $this->docker->containerList()
        );

        if (!empty($names)) {
            $this->dispatcher->dispatch(new ContainerListReceived(...$names));
        }
    }
}
