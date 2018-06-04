<?php
namespace ElevenLabs\DockerHostManager\Event;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class DockerEventReceivedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event->getName(), equalTo('docker.event.received'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_INTERNAL)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new DockerEventReceived(
            (new EventsGetResponse200())
                ->setType('container')
                ->setAction('create')
        );

        assertThat($event->toArray(), equalTo(['type' => 'container', 'action' => 'create']));
    }
}
