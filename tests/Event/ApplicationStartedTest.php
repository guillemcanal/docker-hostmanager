<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ApplicationStartedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new ApplicationStarted();
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new ApplicationStarted();
        assertThat($event->getName(), equalTo('application.started'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new ApplicationStarted();
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new ApplicationStarted();
        assertThat($event->toArray(), equalTo([]));
    }

}