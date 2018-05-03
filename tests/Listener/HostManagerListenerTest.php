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
    public function it support docker containers create events()
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
    public function it support docker containers destroy events()
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
    public function it handle containers create events by adding hosts in the hosts file()
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
        $hostsFileManagerMock->addDomain('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->addDomain('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
        );

        $listener->handle($event);
    }

    /** @test */
    public function it handle containers destroy events by removing hosts in the hosts file()
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
        $hostsFileManagerMock->removeDomain('foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->removeDomain('bar.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new HostManagerListener(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal()
        );

        $listener->handle($event);
    }
}