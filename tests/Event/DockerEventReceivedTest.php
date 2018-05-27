<?php
namespace ElevenLabs\DockerHostManager\Event;

use Docker\API\Model\EventsGetResponse200;
use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class DockerEventReceivedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event->getName(), equalTo('docker.event.received'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new DockerEventReceived(new EventsGetResponse200());
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_INTERNAL)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new DockerEventReceived(
            (new EventsGetResponse200())
                ->setType('container')
                ->setAction('create')
        );

        assertThat($event->toArray(), equalTo(['type' => 'container', 'action' => 'create']));
    }
}
