<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\SignedCertificateCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\File\Directory;
use ElevenLabs\DockerHostManager\File\File;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class CreateSignedCertificateTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $directory = $this->prophesize(Directory::class);
        $listener = new CreateSignedCertificate(
            $certificateGenerator->reveal(),
            $directory->reveal()
        );

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_container_created_event()
    {
        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $directory = $this->prophesize(Directory::class);
        $listener = new CreateSignedCertificate(
            $certificateGenerator->reveal(),
            $directory->reveal()
        );

        assertTrue($listener->subscription()->support(new ContainerCreated('', [], [])));
    }

    /** @test */
    public function it_should_create_a_signed_certificate_and_produce_a_SignedCertificateCreated_event()
    {
        $certificateBundle = $this->getCertificateBundle();

        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $certificateGenerator->generate(['test.domain.fr'])->willReturn($certificateBundle);

        $directory = $this->prophesize(Directory::class);
        $directory
            ->file('certs/test.crt')
            ->willReturn($this->fileMock(
                $expectedCertificateUri = 'file://certs/test.crt',
                (string) $certificateBundle->getCertificate()->toPEM())
            );
        $directory
            ->file('keys/test.key')
            ->willReturn($this->fileMock(
                $expectedKeyUri = 'file://certs/test.key',
                (string) $certificateBundle->getPrivateKeyInfo()->toPEM())
            );

        $listener = new CreateSignedCertificate(
            $certificateGenerator->reveal(),
            $directory->reveal()
        );

        $event = new ContainerCreated('test', ['test.domain.fr'], []);

        $listener->subscription()->handle($event);

        $producedEvents = $listener->producedEvents();

        assertCount(1, $producedEvents);
        assertThat(current($producedEvents), isInstanceOf(SignedCertificateCreated::class));
        assertThat(current($producedEvents)->getContainerName(), equalTo('test'));
        assertThat(current($producedEvents)->getCertificateUri(), equalTo($expectedCertificateUri));
        assertThat(current($producedEvents)->getPrivateKeyUri(), equalTo($expectedKeyUri));
    }

    private function fileMock(string $uri, string $content)
    {
        $file = $this->prophesize(File::class);
        $file->put($content)->shouldBeCalledTimes(1);
        $file->uri()->willReturn($uri);

        return $file;
    }

    private function getCertificateBundle(): CertificateBundle
    {
        return new CertificateBundle(
            Certificate::fromPEM(PEM::fromFile(dirname(__DIR__) . '/Fixtures/signed-cert/cert.crt')),
            PrivateKeyInfo::fromPEM(PEM::fromFile(dirname(__DIR__) . '/Fixtures/signed-cert/cert.key'))
        );
    }
}