<?php
namespace ElevenLabs\DockerHostManager\Event;

use ElevenLabs\DockerHostManager\EventDispatcher\Event;
use ElevenLabs\DockerHostManager\EventDispatcher\EventType;
use PHPUnit\Framework\TestCase;

class SignedCertificateCreatedTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_interface()
    {
        $event = new SignedCertificateCreated('test', ['foo.domain.fr'], '/cert.crt', '/cert.key');
        assertThat($event, isInstanceOf(Event::class));
    }

    /** @test */
    public function it_provide_a_name()
    {
        $event = new SignedCertificateCreated('test', ['foo.domain.fr'], '/cert.crt', '/cert.key');
        assertThat($event->getName(), equalTo('signed.certificate.created'));
    }

    /** @test */
    public function it_provide_a_type()
    {
        $event = new SignedCertificateCreated('test', ['foo.domain.fr'], '/cert.crt', '/cert.key');
        assertThat($event->getType(), equalTo(new EventType(EventType::EVENT_STANDARD)));
    }

    /** @test */
    public function it_can_be_transformed_into_an_array()
    {
        $event = new SignedCertificateCreated('test', ['foo.domain.fr'], '/cert.crt', '/cert.key');
        assertThat(
            $event->toArray(),
            equalTo(
                [
                    'containerName'  => 'test',
                    'domainNames'    => ['foo.domain.fr'],
                    'certificateUri' => '/cert.crt',
                    'privateKeyUri'  => '/cert.key',
                ]
            )
        );
    }

    /** @test */
    public function it_prodive_domain_names()
    {
        $event = new SignedCertificateCreated('test', ['foo.domain.fr'], '/cert.crt', '/cert.key');
        assertThat($event->getDomainNames(), equalTo(['foo.domain.fr']));
    }
}
