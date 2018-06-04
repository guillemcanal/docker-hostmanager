<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\ContainerRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;

class RemoveDomainNamesTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new RemoveDomainNames($hostsFileManager->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_container_removed_event()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new RemoveDomainNames($hostsFileManager->reveal());

        assertTrue($listener->subscription()->support(new ContainerRemoved('', [])));
    }

    /** @test */
    public function it_can_remove_a_domain_name_from_the_hosts_file()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->removeDomainName($expectedDomainName)->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new RemoveDomainNames($hostsFileManager->reveal());

        $listener->subscription()->handle(new ContainerRemoved('test', ['test.domain.fr']));
    }
}