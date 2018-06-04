<?php

namespace ElevenLabs\DockerHostManager;

use Closure;
use Docker\API\Model\ContainerSummaryItem;
use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Event\ApplicationStarted;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DockerEventsTest extends TestCase
{
    /** @test */
    public function it_produce_a_DockerEventReceived_event_when_a_docker_event_is_received()
    {
        $dockerEvent = new EventsGetResponse200();

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->wait()->shouldBeCalledTimes(1);
        $eventStream->onFrame(Argument::type(Closure::class))->will(
            function (array $args) use ($dockerEvent) {
                $callbackFunction = current($args);
                $callbackFunction($dockerEvent);
            }
        );

        $docker = $this->prophesize(Docker::class);
        $docker->systemEvents(Argument::any())->willReturn($eventStream);
        $docker->containerList()->willReturn([]);

        $dispatcher = $this->prophesize(EventDispatcher::class);
        $dispatcher->dispatch(new ApplicationStarted())->shouldBeCalledTimes(1);
        $dispatcher->dispatch(new DockerEventReceived($dockerEvent))->shouldBeCalledTimes(1);

        $dockerEvents = new DockerEvents($docker->reveal(), $dispatcher->reveal());
        $dockerEvents->listen();
    }

    /** @test */
    public function it_produce_a_ContainerListReceived_event_when_started()
    {
        $containerList = [(new ContainerSummaryItem())->setNames(['/test-container'])];

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->wait();
        $eventStream->onFrame(Argument::type(Closure::class));

        $docker = $this->prophesize(Docker::class);
        $docker->systemEvents(Argument::any())->willReturn($eventStream);
        $docker->containerList()->willReturn($containerList);

        $dispatcher = $this->prophesize(EventDispatcher::class);
        $dispatcher->dispatch(new ApplicationStarted())->shouldBeCalledTimes(1);
        $dispatcher->dispatch(new ContainerListReceived(new Container('test-container', null)))->shouldBeCalledTimes(1);

        $dockerEvents = new DockerEvents($docker->reveal(), $dispatcher->reveal());
        $dockerEvents->listen();
    }
}
