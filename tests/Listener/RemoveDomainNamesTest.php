<?php

namespace ElevenLabs\DockerHostManager\Listener;

use ElevenLabs\DockerHostManager\DomainName;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use ElevenLabs\DockerHostManager\HostsFileManager;
use PHPUnit\Framework\TestCase;

class RemoveDomainNamesTest extends TestCase
{
    /** @test */
    public function it subscribe to the DomainNamesRemoved event()
    {
        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $listener = new RemoveDomainNames($hostsFileManager->reveal());

        assertTrue($listener->subscription()->support(new DomainNamesRemoved('', [])));
    }

    /** @test */
    public function it can remove a domain name from the hosts file()
    {
        $expectedDomainName = new DomainName('test.domain.fr', 'test');

        $hostsFileManager = $this->prophesize(HostsFileManager::class);
        $hostsFileManager->removeDomainName($expectedDomainName)->shouldBeCalledTimes(1);
        $hostsFileManager->updateHostsFile()->shouldBeCalledTimes(1);

        $listener = new RemoveDomainNames($hostsFileManager->reveal());

        $listener->subscription()->handle(new DomainNamesRemoved('test', ['test.domain.fr']));
    }
}