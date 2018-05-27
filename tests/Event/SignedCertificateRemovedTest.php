<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class SignedCertificateRemovedTest extends TestCase
{
    /** @test */
    public function it implements the event interface()
    {
        $event = new SignedCertificateRemoved('test');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it provide a name()
    {
        $event = new SignedCertificateRemoved('test');
        assertThat($event->getName(), equalTo('signed.certificate.removed'));
    }

    /** @test */
    public function it provide a type()
    {
        $event = new SignedCertificateRemoved('test');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it can be transformed into an array()
    {
        $event = new SignedCertificateRemoved('test');
        assertThat($event->toArray(), equalTo(['containerName' => 'test']));
    }
}