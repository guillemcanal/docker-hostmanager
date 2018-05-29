<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ApplicationStartedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new ApplicationStarted();
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new ApplicationStarted();
        assertThat($event->getName(), equalTo('application.started'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new ApplicationStarted();
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new ApplicationStarted();
        assertThat($event->toArray(), equalTo([]));
    }

}