<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class DomainNamesRemovedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new DomainNamesRemoved('test', ['foo.domain.fr']);
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new DomainNamesRemoved('test', ['foo.domain.fr']);
        assertThat($event->getName(), equalTo('domain.names.removed'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new DomainNamesRemoved('test', ['foo.domain.fr']);
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new DomainNamesRemoved('test', ['foo.domain.fr']);
        assertThat($event->toArray(), equalTo(['containerName' => 'test', 'domainNames' => ['foo.domain.fr']]));
    }
}
