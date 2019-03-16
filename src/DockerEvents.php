<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Event\ApplicationStarted;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventDispatcher;

class DockerEvents
{
    /** @var Docker */
    private $docker;

    /** @var EventDispatcher */
    private $dispatcher;

    private $events;

    public function __construct(Docker $docker, EventDispatcher $dispatcher)
    {
        $this->docker = $docker;
        $this->dispatcher = $dispatcher;
    }

    public function listen(): void
    {
        $this->applicationStarted();
        $this->listContainerNames();
        /** @var EventStream $events */
        $this->events = $this->docker->systemEvents();
        $this->events->onFrame(
            function ($event): void {
                if (\is_object($event) && $event instanceof EventsGetResponse200) {
                    $this->dispatcher->dispatch(new DockerEventReceived($event));
                }
            }
        );
        $this->events->wait();
    }

    public function close(): void
    {
        $this->events->closeAndRead();
    }

    private function listContainerNames(): void
    {
        $names = \array_map(
            function (ContainerSummaryItem $item) {
                $containerName = \ltrim(\current($item->getNames()), '/');
                $containerLabels = $item->getLabels() ?: new \ArrayObject();

                return new Container($containerName, \iterator_to_array($containerLabels));
            },
            $this->docker->containerList()
        );

        if (!empty($names)) {
            $this->dispatcher->dispatch(new ContainerListReceived(...$names));
        }
    }

    private function applicationStarted(): void
    {
        $this->dispatcher->dispatch(new ApplicationStarted());
    }
}
