<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class LogEventsTest extends TestCase
{
    /** @test */
    public function it subscribe to any events()
    {
        $event = $this->prophesize(Event::class);
        $listener = new LogEvents(new NullLogger());

        assertThat($listener, isInstanceOf(EventListener::class));
        assertThat($listener->subscription()->support($event->reveal()), isTrue());
    }

    /** @test */
    public function it can log a dispatched event()
    {
        $event = $this->prophesize(Event::class);
        $event->getName()->willReturn('test.event');
        $event->getType()->willReturn(new EventType(EventType::EVENT_STANDARD));
        $event->toArray()->willReturn(['name' => 'value']);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->log(LogLevel::INFO, 'test.event', ['name' => 'value'])->shouldBeCalledTimes(1);

        $listener = new LogEvents($logger->reveal());
        $listener->subscription()->handle($event->reveal());
    }
}
