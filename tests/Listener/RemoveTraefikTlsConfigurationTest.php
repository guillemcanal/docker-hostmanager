<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
use PHPUnit\Framework\TestCase;

class RemoveTraefikTlsConfigurationTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new RemoveTraefikTlsConfiguration($directory->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_signed_certificate_created_event()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new RemoveTraefikTlsConfiguration($directory->reveal());

        assertThat($listener->subscription()->support(new SignedCertificateRemoved('')), isTrue());
    }

    /** @test */
    public function it_remove_the_traefik_tls_configuration_from_the_filesystem()
    {
        $event = new SignedCertificateRemoved('test-container');

        $tomlConfiguration = $this->prophesize(File::class);
        $tomlConfiguration->exists()->willReturn(true);
        $tomlConfiguration->delete()->shouldBeCalledTimes(1);

        $traefikDirectory = $this->prophesize(Directory::class);
        $traefikDirectory->file('test-container.tls.toml')->willReturn($tomlConfiguration);

        $directory = $this->prophesize(Directory::class);
        $directory->directory('traefik')->willReturn($traefikDirectory);

        $listener = new RemoveTraefikTlsConfiguration($directory->reveal());

        $listener->subscription()->handle($event);
    }
}
