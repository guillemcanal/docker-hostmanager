<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use PHPUnit\Framework\TestCase;

class UpdateHostsFileTest extends TestCase
{
    /** @test */
    public function it supports docker container create events()
    {
        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsExtractorMock = $this->prophesize(DomainNameExtractor::class);

        $listener = new UpdateHostsFile(
            $hostsFileManagerMock->reveal(),
            $hostsExtractorMock->reveal()
        );

        $event = $this->prophesize(EventsGetResponse200::class);
        $event->getType()->willReturn('container');
        $event->getAction()->willReturn('create');

        assertTrue($listener->support($event->reveal()));
    }

    /** @test */
    public function it supports docker container destroy events()
    {
        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsExtractorMock = $this->prophesize(DomainNameExtractor::class);

        $listener = new UpdateHostsFile(
            $hostsFileManagerMock->reveal(),
            $hostsExtractorMock->reveal()
        );

        $event = $this->prophesize(EventsGetResponse200::class);
        $event->getType()->willReturn('container');
        $event->getAction()->willReturn('destroy');

        assertTrue($listener->support($event->reveal()));
    }

    /** @test */
    public function it handle container create events by adding domain names in the hosts file()
    {
        $actor = new EventsGetResponse200Actor();
        $actor->setAttributes($attributes = new \ArrayObject());

        $event = new EventsGetResponse200();
        $event->setAction('create');
        $event->setActor($actor);

        $hostsExtractorMock = $this->prophesize(DomainNameExtractor::class);
        $hostsExtractorMock->provideDomainNames($attributes)->willReturn(true);
        $hostsExtractorMock->getDomainNames($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->addDomainName('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->addDomainName('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new UpdateHostsFile(
            $hostsFileManagerMock->reveal(),
            $hostsExtractorMock->reveal()
        );

        $listener->handle($event);
    }

    /** @test */
    public function it handle container destroy events by removing hostnames in the hosts file()
    {
        $actor = new EventsGetResponse200Actor();
        $actor->setAttributes($attributes = new \ArrayObject());

        $event = new EventsGetResponse200();
        $event->setAction('destroy');
        $event->setActor($actor);

        $hostsExtractorMock = $this->prophesize(DomainNameExtractor::class);
        $hostsExtractorMock->provideDomainNames($attributes)->willReturn(true);
        $hostsExtractorMock->getDomainNames($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->removeDomainName('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->removeDomainName('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new UpdateHostsFile(
            $hostsFileManagerMock->reveal(),
            $hostsExtractorMock->reveal()
        );

        $listener->handle($event);
    }
}