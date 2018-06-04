<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\ContainerRemoved;
use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class DeleteSignedCertificateTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new DeleteSignedCertificate($directory->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_container_removed_event()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new DeleteSignedCertificate($directory->reveal());

        assertTrue($listener->subscription()->support(new ContainerRemoved('', [])));
    }

    /** @test */
    public function it_should_remove_a_signed_certificate_and_produce_a_signed_certificate_removed_event()
    {
        $directory = $this->prophesize(Directory::class);
        $directory->file('certs/test.crt')->willReturn($this->fileMock());
        $directory->file('keys/test.key')->willReturn($this->fileMock());

        $listener = new DeleteSignedCertificate($directory->reveal());

        $event = new ContainerRemoved('test', ['test.domain.fr']);

        $listener->subscription()->handle($event);

        $producedEvents = $listener->producedEvents();

        assertCount(1, $producedEvents);
        assertThat(current($producedEvents), isInstanceOf(SignedCertificateRemoved::class));
        assertThat(current($producedEvents)->getContainerName(), equalTo('test'));
    }

    private function fileMock()
    {
        $file = $this->prophesize(File::class);
        $file->exists()->willReturn(true);
        $file->delete()->shouldBeCalledTimes(1);

        return $file;
    }
}