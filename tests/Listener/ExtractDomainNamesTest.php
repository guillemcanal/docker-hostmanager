<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\Event\DomainNamesAdded;
use ElevenLabs\DockerHostManager\Event\DomainNamesRemoved;
use PHPUnit\Framework\TestCase;

class ExtractDomainNamesTest extends TestCase
{
    /** @test */
    public function it subcribe to the DockerEventReceived event()
    {
        $listener = new ExtractDomainNames();

        assertTrue($listener->subscription()->support(new DockerEventReceived(new EventsGetResponse200())));
    }

    /**
     * @test
     * @dataProvider getSupportedContainerDockerEvents
     */
    public function it handle(string $containerAction, string $expectedProducedEvent)
    {
        $containerAttributes = new \ArrayObject(['name' => 'test-container']);

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($containerAttributes)->willReturn(true);
        $domainNameExtractor->getDomainNames($containerAttributes)->willReturn(['test.domain.fr']);

        $listener = new ExtractDomainNames($domainNameExtractor->reveal());

        $event = new EventsGetResponse200();
        $event
            ->setType('container')
            ->setAction($containerAction)
            ->setActor((new EventsGetResponse200Actor())->setAttributes($containerAttributes));

        $listener->subscription()->handle(new DockerEventReceived($event));

        $producedEvents = $listener->producedEvents();

        assertCount(1, $producedEvents);
        assertThat(current($producedEvents), isInstanceOf($expectedProducedEvent));
        assertThat(current($producedEvents)->getContainerName(), equalTo('test-container'));
        assertThat(current($producedEvents)->getDomainNames(), equalTo(['test.domain.fr']));
    }

    public function  getSupportedContainerDockerEvents(): array
    {
        return [
            'docker container create event'  => ['create', DomainNamesAdded::class],
            'docker container destroy event' => ['destroy', DomainNamesRemoved::class],
        ];
    }
}