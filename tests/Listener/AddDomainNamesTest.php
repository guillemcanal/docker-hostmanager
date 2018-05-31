<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\ErrorReceived;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;

class AddDomainNamesTest extends TestCase
{
    /** @test */
    public function it subcribe to the DomainNamesAdded event()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new AddDomainNames($hostsFileManager->reveal());

        assertTrue($listener->subscription()->support(new DomainNamesAdded('', [], [])));
    }

    /** @test */
    public function it should add a domain name into the hosts file()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test-container');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->addDomainName($expectedDomainName)->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new AddDomainNames($hostsFileManager->reveal());

        $listener->subscription()->handle(new DomainNamesAdded('test-container', ['test.domain.fr'], []));
    }

    /** @test */
    public function it should produce an error event when trying to add a domain name that already exists()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->addDomainName($expectedDomainName)->willThrow($thrownException = new \UnexpectedValueException());
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new AddDomainNames($hostsFileManager->reveal());
        $listener->subscription()->handle(new DomainNamesAdded('test', ['test.domain.fr'], []));

        $producedEvents = $listener->producedEvents();
        $expectedErrorMessage = 'Unable to add domain name test.domain.fr for container test';

        assertCount(2, $producedEvents);
        assertThat(current($producedEvents), isInstanceOf(ErrorReceived::class));
        assertThat(current($producedEvents)->getMessage(), equalTo($expectedErrorMessage));
        assertThat(current($producedEvents)->getException(), equalTo($thrownException));
    }
}