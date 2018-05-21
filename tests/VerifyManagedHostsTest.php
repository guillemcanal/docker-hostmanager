<?php

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use ElevenLabs\DockerHostManager\HostsExtractor\HostsExtractor;
use PHPUnit\Framework\TestCase;

class VerifyManagedHostsTest extends TestCase
{
    /** @test */
    public function it verify that the hosts file is in sync()
    {
        $containersSummaries = [
            (new ContainerSummaryItem())->setLabels($containerLabels = new \ArrayObject(['hostname' => 'dev.foo.fr']))
        ];

        $dockerClientMock = $this->prophesize(Docker::class);
        $dockerClientMock->containerList()->willReturn($containersSummaries);

        $hostsProviderMock = $this->prophesize(HostsExtractor::class);
        $hostsProviderMock->hasHosts($containerLabels)->willReturn(true);
        $hostsProviderMock->getHosts($containerLabels)->willReturn(['dev.foo.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->clear()->shouldBeCalledTimes(1);
        $hostsFileManagerMock->addHostname('dev.foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $verifyHosts = new VerifyManagedHosts(
            $hostsFileManagerMock->reveal(),
            $hostsProviderMock->reveal(),
            $dockerClientMock->reveal()
        );

        $verifyHosts->verify();
    }
}