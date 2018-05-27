<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;

class CleanTheHostsFileTest extends TestCase
{
    /** @test */
    public function it subscribe to the ContainerListReceived events()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $event = $this->prophesize(ContainerListReceived::class);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
        assertThat($listener->subscription()->support($event->reveal()), isTrue());
    }

    /** @test */
    public function it produce a DomainNamesRemoved event when it cant find it in the hosts file()
    {
        $aDomainNamesFromHostsFile = new DomainName('foo.domain.fr', 'unexisting-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->getDomainNames()->willReturn([$aDomainNamesFromHostsFile]);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());
        $listener->subscription()->handle(new ContainerListReceived('existing-container'));

        $producedEvents = $listener->producedEvents();

        assertThat($producedEvents, countOf(1));
        assertThat(current($producedEvents), isInstanceOf(DomainNamesRemoved::class));
        assertThat(current($producedEvents)->getContainerName(), equalTo('unexisting-container'));
        assertThat(current($producedEvents)->getDomainNames(), equalTo(['foo.domain.fr']));
    }

    /** @test */
    public function it preserve a container domain name if is listed in the container list()
    {
        $aDomainNamesFromHostsFile = new DomainName('foo.domain.fr', 'existing-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->getDomainNames()->willReturn([$aDomainNamesFromHostsFile]);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());
        $listener->subscription()->handle(new ContainerListReceived('existing-container'));

        $producedEvents = $listener->producedEvents();

        assertThat($producedEvents, countOf(0));
    }
}