<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class EventProcessedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new EventProcessed('test');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new EventProcessed('test');
        assertThat($event->getName(), equalTo('event.processed'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new EventProcessed('test');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new EventProcessed('test');
        assertThat($event->toArray(), equalTo(['message' => 'test']));
    }
}
