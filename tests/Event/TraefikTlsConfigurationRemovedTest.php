<?php

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class TraefikTlsConfigurationRemovedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->getName(), equalTo('traefik.tls.configuration.removed'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->toArray(), equalTo(['containerName' => 'test']));
    }
}
