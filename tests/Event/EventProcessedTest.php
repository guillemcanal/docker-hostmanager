<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class EventProcessedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new EventProcessed('test');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new EventProcessed('test');
        assertThat($event->getName(), equalTo('event.processed'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new EventProcessed('test');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new EventProcessed('test');
        assertThat($event->toArray(), equalTo(['message' => 'test']));
    }
}
