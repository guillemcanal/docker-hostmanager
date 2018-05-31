<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\Container;
use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class ContainerListReceivedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new ContainerListReceived(new Container('test', null));
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new ContainerListReceived(new Container('test', null));
        assertThat($event->getName(), equalTo('container.list.received'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new ContainerListReceived(new Container('test', null));
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_INTERNAL)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new ContainerListReceived(new Container('test', null));
        assertThat($event->toArray(), equalTo(['containerList' => ['test']]));
    }

}