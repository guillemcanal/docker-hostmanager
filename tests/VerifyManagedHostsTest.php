<?php

namespace ElevenLabs\DockerHostManager;

use Docker\API\Model\ContainerSummaryItem;
use Docker\Docker;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
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

        $hostsProviderMock = $this->prophesize(DomainNameExtractor::class);
        $hostsProviderMock->provideDomainNames($containerLabels)->willReturn(true);
        $hostsProviderMock->getDomainNames($containerLabels)->willReturn(['dev.foo.fr']);

        $hostsFileManagerMock = $this->prophesize(HostsFileManager::class);
        $hostsFileManagerMock->clear()->shouldBeCalledTimes(1);
        $hostsFileManagerMock->addDomainName('dev.foo.fr')->shouldBeCalledTimes(1);
        $hostsFileManagerMock->updateHostsFile()->shouldBeCalledTimes(1);

        $verifyHosts = new VerifyManagedHosts(
            $hostsFileManagerMock->reveal(),
            $dockerClientMock->reveal(),
            $hostsProviderMock->reveal()
        );

        $verifyHosts->verify();
    }
}