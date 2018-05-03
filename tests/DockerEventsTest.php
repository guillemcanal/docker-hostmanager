<?php

namespace ElevenLabs\DockerHostManager;

use Closure;
use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Listener\DockerEventListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DockerEventsTest extends TestCase
{
    /**
     * @test
     * @group events
     */
    public function it call a docker event listener if it support a given docker event()
    {
        $dockerEvent = new EventsGetResponse200();

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->wait()->shouldBeCalledTimes(1);
        $eventStream->onFrame(Argument::type(Closure::class))->will(
            function (array $args) use ($dockerEvent) {
                \call_user_func(current($args), $dockerEvent);
            }
        );

        $dockerMock = $this->prophesize(Docker::class);
        $dockerMock->systemEvents()->willReturn($eventStream);

        $dockerEventListenerMock = $this->prophesize(DockerEventListener::class);
        $dockerEventListenerMock->support($dockerEvent)->willReturn(true);
        $dockerEventListenerMock->handle($dockerEvent)->shouldBeCalledTimes(1);

        $dockerEvents = new DockerEvents($dockerMock->reveal());
        $dockerEvents->addListener($dockerEventListenerMock->reveal());
        $dockerEvents->run();
    }
}