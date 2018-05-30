<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
use PHPUnit\Framework\TestCase;

class CreateTraefikTlsConfigurationTest extends TestCase
{
    /** @test */
    public function it implements EventListener()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new CreateTraefikTlsConfiguration($directory->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it subscribe to the SignedCertificateCreated event()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new CreateTraefikTlsConfiguration($directory->reveal());

        assertThat(
            $listener->subscription()->support(
                new SignedCertificateCreated('', [], '', '')
            ),
            isTrue()
        );
    }

    /** @test */
    public function it create a tls configuration for traefik when a certificate has been created()
    {
        $expectedTlsConfig = <<<TOML
[[tls]]
  entryPoints = ["https"]
  [tls.certificate]
    certFile = "/data/certs/test-container.crt"
    keyFile = "/data/keys/test-container.key"

TOML;

        $event = new SignedCertificateCreated('test-container', [], '', '');

        $directory = $this->prophesize(Directory::class);

        $certFile = $this->prophesize(File::class);
        $certFile->path()->willReturn('/data/certs/test-container.crt');
        $directory->file('certs/test-container.crt')->willReturn($certFile);

        $keyFile = $this->prophesize(File::class);
        $keyFile->path()->willReturn('/data/keys/test-container.key');
        $directory->file('keys/test-container.key')->willReturn($keyFile);

        $traefikContainerConf = $this->prophesize(File::class);
        $traefikContainerConf->put($expectedTlsConfig)->shouldBeCalledTimes(1);

        $traefikDirectory = $this->prophesize(Directory::class);
        $traefikDirectory->file('test-container.tls.toml')->willReturn($traefikContainerConf);
        $directory->directory('traefik')->willReturn($traefikDirectory);

        $listener = new CreateTraefikTlsConfiguration($directory->reveal());

        $listener->subscription()->handle($event);
    }
}
