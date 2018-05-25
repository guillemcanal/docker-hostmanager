<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\Cert\CertificateBundle;
use ElevenLabs\DockerHostManager\CertificateGenerator;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\File\FileFactory;
use ElevenLabs\DockerHostManager\File\LocalFile;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;

class GenerateSignedCertificateTest extends TestCase
{
    /**
     * @test
     * @dataProvider getSupportedEvents
     */
    public function it support(EventsGetResponse200 $event)
    {
        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $fileFactory = $this->prophesize(FileFactory::class);
        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);

        $listener = new GenerateSignedCertificate(
            $certificateGenerator->reveal(),
            $fileFactory->reveal(),
            $domainNameExtractor->reveal()
        );

        assertTrue($listener->support($event));
    }

    public function getSupportedEvents(): array
    {
        return [
            'docker container create event' => [
                (new EventsGetResponse200())->setType('container')->setAction('create')
            ],
            'docker container destroy event' => [
                (new EventsGetResponse200())->setType('container')->setAction('destroy')
            ],
        ];
    }

    /** @test */
    public function it ignore an event without an actor()
    {
        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $fileFactory = $this->prophesize(FileFactory::class);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames(Argument::any())->shouldNotBeCalled();

        $listener = new GenerateSignedCertificate(
            $certificateGenerator->reveal(),
            $fileFactory->reveal(),
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create');

        $listener->handle($event);
    }

    /** @test */
    public function it ignore a container without attributes()
    {
        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $fileFactory = $this->prophesize(FileFactory::class);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames(Argument::any())->shouldNotBeCalled();

        $listener = new GenerateSignedCertificate(
            $certificateGenerator->reveal(),
            $fileFactory->reveal(),
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create')
            ->setActor(new EventsGetResponse200Actor());

        $listener->handle($event);
    }

    /** @test */
    public function it ignore a container that does not provide dns names()
    {
        $containerAttributes = new \ArrayObject(['name' => 'test']);

        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $certificateGenerator->generate(Argument::any())->shouldNotBeCalled();

        $fileFactory = $this->prophesize(FileFactory::class);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($containerAttributes)->willReturn(false);

        $listener = new GenerateSignedCertificate(
            $certificateGenerator->reveal(),
            $fileFactory->reveal(),
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create')
            ->setActor(
                (new EventsGetResponse200Actor())->setAttributes($containerAttributes)
            );

        $listener->handle($event);
    }

    /** @test */
    public function it generate and save a signed certificate to the filesystem()
    {
        $containerAttributes  = new \ArrayObject(['name' => 'container-test']);
        $extractedDomainNames = ['foo.domain.fr', 'bar.domain.fr'];

        $fixturesDir = \dirname(__DIR__) . '/Fixtures/signed-cert';
        $certificateBundle = new CertificateBundle(
            Certificate::fromPEM(PEM::fromFile($fixturesDir . '/cert.crt')),
            PrivateKeyInfo::fromPEM(PEM::fromFile($fixturesDir . '/cert.key'))
        );

        $certificateGenerator = $this->prophesize(CertificateGenerator::class);
        $certificateGenerator->generate($extractedDomainNames)->willReturn($certificateBundle);

        $rootDirectory = vfsStream::setup('data', 0755);
        $fileFactory = new FileFactory(LocalFile::class, $rootDirectory->url());

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($containerAttributes)->willReturn(true);
        $domainNameExtractor->getDomainNames($containerAttributes)->willReturn($extractedDomainNames);

        $listener = new GenerateSignedCertificate(
            $certificateGenerator->reveal(),
            $fileFactory,
            $domainNameExtractor->reveal()
        );

        $event = (new EventsGetResponse200())
            ->setType('container')
            ->setAction('create')
            ->setActor(
                (new EventsGetResponse200Actor())->setAttributes($containerAttributes)
            );

        $listener->handle($event);

        assertFileExists($certFile = $rootDirectory->url() . '/certs/container-test.crt');
        assertFileExists($certKeyFile = $rootDirectory->url() . '/keys/container-test.key');

        assertStringEqualsFile($certFile, (string) $certificateBundle->getCertificate()->toPEM());
        assertStringEqualsFile($certKeyFile, (string) $certificateBundle->getPrivateKeyInfo()->toPEM());
    }
}