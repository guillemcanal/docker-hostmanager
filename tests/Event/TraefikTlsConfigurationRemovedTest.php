<?php

namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class TraefikTlsConfigurationRemovedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->getName(), equalTo('traefik.tls.configuration.removed'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new TraefikTlsConfigurationRemoved('test');
        assertThat($event->toArray(), equalTo(['containerName' => 'test']));
    }
}
