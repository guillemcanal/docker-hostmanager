<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\ErrorReceived;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;

class AddDomainNamesTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new AddDomainNames($hostsFileManager->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subcribe_to_the_container_created_event()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new AddDomainNames($hostsFileManager->reveal());

        assertTrue($listener->subscription()->support(new ContainerCreated('', [], [])));
    }

    /** @test */
    public function it_should_add_a_domain_name_into_the_hosts_file()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->addDomainName($expectedDomainName)->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new AddDomainNames($hostsFileManager->reveal());

        $listener->subscription()->handle(new ContainerCreated('test-container', ['test.domain.fr'], []));
    }

    /** @test */
    public function it_should_produce_an_error_event_when_trying_to_add_a_domain_name_that_already_exists()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->addDomainName($expectedDomainName)->willThrow($thrownException = new \UnexpectedValueException());
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new AddDomainNames($hostsFileManager->reveal());
        $listener->subscription()->handle(new ContainerCreated('test', ['test.domain.fr'], []));

        $producedEvents = $listener->producedEvents();
        $expectedErrorMessage = 'Unable to add domain name test.domain.fr for container test';

        assertCount(2, $producedEvents);
        assertThat(current($producedEvents), isInstanceOf(ErrorReceived::class));
        assertThat(current($producedEvents)->getMessage(), equalTo($expectedErrorMessage));
        assertThat(current($producedEvents)->getException(), equalTo($thrownException));
    }
}