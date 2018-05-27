<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\Event\SignedCertificateRemoved;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class DeleteSignedCertificateTest extends TestCase
{
    /** @test */
    public function it subscribe to the DomainNamesRemoved event()
    {
        $directory = $this->prophesize(Directory::class);
        $listener = new DeleteSignedCertificate($directory->reveal());

        assertTrue($listener->subscription()->support(new DomainNamesRemoved('', [])));
    }

    /** @test */
    public function it should remove a signed certificate and produce a SignedCertificateDeleted event()
    {
        $directory = $this->prophesize(Directory::class);
        $directory->file('certs/test.crt')->willReturn($this->fileMock());
        $directory->file('keys/test.key')->willReturn($this->fileMock());

        $listener = new DeleteSignedCertificate($directory->reveal());

        $event = new DomainNamesRemoved('test', ['test.domain.fr']);

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