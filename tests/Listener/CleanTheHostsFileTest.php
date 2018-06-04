<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\Container;
use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\ContainerListReceived;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\ContainerRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CleanTheHostsFileTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new CleanTheHostsFile($hostsFileManager->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_container_list_received_events()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $event = $this->prophesize(ContainerListReceived::class);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
        assertThat($listener->subscription()->support($event->reveal()), isTrue());
    }

    /** @test */
    public function it_produce_a_container_removed_event_when_it_cant_find_it_in_the_hosts_file()
    {
        $aDomainNamesFromHostsFile = new DomainName('foo.domain.fr', 'unexisting-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->getDomainNames()->willReturn([$aDomainNamesFromHostsFile]);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());
        $listener->subscription()->handle(new ContainerListReceived(new Container('existing-container', null)));

        $producedEvents = $listener->producedEvents();

        assertThat($producedEvents, countOf(1));
        assertThat(current($producedEvents), isInstanceOf(ContainerRemoved::class));
        assertThat(current($producedEvents)->getContainerName(), equalTo('unexisting-container'));
        assertThat(current($producedEvents)->getDomainNames(), equalTo(['foo.domain.fr']));
    }

    /** @test */
    public function it_preserve_a_container_domain_name_if_is_listed_in_the_container_list()
    {
        $aDomainNamesFromHostsFile = new DomainName('foo.domain.fr', 'existing-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->getDomainNames()->willReturn([$aDomainNamesFromHostsFile]);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal());
        $listener->subscription()->handle(new ContainerListReceived(new Container('existing-container', null)));

        $producedEvents = $listener->producedEvents();

        assertThat($producedEvents, countOf(0));
    }

    /** @test */
    public function it_add_domain_names_in_the_hosts_file_when_absent()
    {
        $containerLabelsFromARunningContainer = ['docker.hostmanager.names' => 'foo.fr,bar.fr'];

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->getDomainNames()->willReturn([]);
        $hostsFileManager->hasDomainName(
            Argument::allOf(
                Argument::type(DomainName::class),
                Argument::which('getContainerName', 'absent-container')
            )
        )->willReturn(false);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($containerLabelsFromARunningContainer)->willReturn(true);
        $domainNameExtractor->getDomainNames($containerLabelsFromARunningContainer)->willReturn(['foo.fr', 'bar.fr']);

        $listener = new CleanTheHostsFile($hostsFileManager->reveal(), $domainNameExtractor->reveal());
        $listener->subscription()->handle(
            new ContainerListReceived(
                new Container('absent-container', $containerLabelsFromARunningContainer)
            )
        );

        $producedEvents = $listener->producedEvents();

        assertThat($producedEvents, countOf(1));
        assertThat(current($producedEvents), isInstanceOf(ContainerCreated::class));
        assertThat(current($producedEvents)->getContainerName(), equalTo('absent-container'));
        assertThat(current($producedEvents)->getDomainNames(), equalTo(['foo.fr', 'bar.fr']));
    }
}