<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\HostsFileManager;
use ElevenLabs\DockerHostManager\HostsProvider\HostsProvider;
use PHPUnit\Framework\TestCase;

class HostManagerListenerTest extends TestCase
{
    /** @test */
    public function it supports docker container create events()
    {
        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsProviderMock = $this->prophesize(HostsProvider::class);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
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
        $hostsProviderMock = $this->prophesize(HostsProvider::class);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
        );

        $event = $this->prophesize(EventsGetResponse200::class);
        $event->getType()->willReturn('container');
        $event->getAction()->willReturn('destroy');

        assertTrue($listener->support($event->reveal()));
    }

    /** @test */
    public function it handle container create events by adding hostnames in the hosts file()
    {
        $actor = new EventsGetResponse200Actor();
        $actor->setAttributes($attributes = new \ArrayObject());

        $event = new EventsGetResponse200();
        $event->setAction('create');
        $event->setActor($actor);

        $hostsProviderMock = $this->prophesize(HostsProvider::class);
        $hostsProviderMock->hasHosts($attributes)->willReturn(true);
        $hostsProviderMock->getHosts($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->addHostname('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->addHostname('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
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

        $hostsProviderMock = $this->prophesize(HostsProvider::class);
        $hostsProviderMock->hasHosts($attributes)->willReturn(true);
        $hostsProviderMock->getHosts($attributes)->willReturn(['foo.fr', 'bar.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->removeHostname('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->removeHostname('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
        );

        $listener->handle($event);
    }
}