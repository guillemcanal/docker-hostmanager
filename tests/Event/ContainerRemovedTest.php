<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ContainerRemovedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new ContainerRemoved('test', ['foo.domain.fr']);
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new ContainerRemoved('test', ['foo.domain.fr']);
        assertThat($event->getName(), equalTo('container.removed'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new ContainerRemoved('test', ['foo.domain.fr']);
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new ContainerRemoved('test', ['foo.domain.fr']);
        assertThat($event->toArray(), equalTo(['containerName' => 'test', 'domainNames' => ['foo.domain.fr']]));
    }
}
