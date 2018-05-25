<?php

namespace ElevenLabs\DockerHostManager;

use Closure;
use Docker\API\Model\EventsGetResponse200;
use Docker\Docker;
use Docker\Stream\EventStream;
use ElevenLabs\DockerHostManager\Listener\DockerEvent;
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
        $dockerMock->systemEvents([])->willReturn($eventStream);

        $dockerEventListenerMock = $this->prophesize(DockerEvent::class);
        $dockerEventListenerMock->support($dockerEvent)->willReturn(true);
        $dockerEventListenerMock->handle($dockerEvent)->shouldBeCalledTimes(1);

        $dockerEvents = new DockerEvents($dockerMock->reveal());
        $dockerEvents->addListener($dockerEventListenerMock->reveal());
        $dockerEvents->listen();
    }

    /** @test */
    public function it can listen to docker events since a given time in seconds()
    {
        $timeInseconds = 5;
        $expectedTime  = \time() - $timeInseconds;

        $expectedSystemEventsOptions = Argument::that(
            function (array $options) use ($expectedTime) {
                return array_key_exists('since', $options)
                    // make an approximation since we are depending on \time()
                    && $options['since'] <= $expectedTime + 1
                    && $options['since'] >= $expectedTime - 1;
            }
        );

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->wait()->shouldBeCalledTimes(1);
        $eventStream->onFrame(Argument::type(Closure::class))->shouldBeCalledTimes(1);

        $dockerMock = $this->prophesize(Docker::class);
        $dockerMock->systemEvents($expectedSystemEventsOptions)->willReturn($eventStream);

        $dockerEvents = new DockerEvents($dockerMock->reveal());
        $dockerEvents->listenSince($timeInseconds);
        $dockerEvents->listen();
    }

    /** @test */
    public function it can listen to docker events until a given time in seconds()
    {
        $timeInseconds = 5;
        $expectedTime  = \time() + $timeInseconds;

        $expectedSystemEventsOptions = Argument::that(
            function (array $options) use ($expectedTime) {
                return array_key_exists('until', $options)
                    // make an approximation since we are depending on \time()
                    && $options['until'] <= $expectedTime + 1
                    && $options['until'] >= $expectedTime - 1;
            }
        );

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->wait()->shouldBeCalledTimes(1);
        $eventStream->onFrame(Argument::type(Closure::class))->shouldBeCalledTimes(1);

        $dockerMock = $this->prophesize(Docker::class);
        $dockerMock->systemEvents($expectedSystemEventsOptions)->willReturn($eventStream);

        $dockerEvents = new DockerEvents($dockerMock->reveal());
        $dockerEvents->listenUntil($timeInseconds);
        $dockerEvents->listen();
    }
}