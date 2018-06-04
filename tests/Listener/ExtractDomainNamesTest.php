<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\EventsGetResponse200;
use Docker\API\Model\EventsGetResponse200Actor;
use ElevenLabs\DockerHostManager\DomainNameExtractor\DomainNameExtractor;
use ElevenLabs\DockerHostManager\Event\DockerEventReceived;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\Event\ContainerRemoved;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use PHPUnit\Framework\TestCase;

class ExtractDomainNamesTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $listener = new ExtractDomainNames($domainNameExtractor->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subcribe_to_the_docker_event_received_event()
    {
        $listener = new ExtractDomainNames();

        assertTrue($listener->subscription()->support(new DockerEventReceived(new EventsGetResponse200())));
    }

    /**
     * @test
     * @dataProvider getSupportedContainerDockerEvents
     */
    public function it_handle(string $containerAction, string $expectedProducedEvent)
    {
        $containerAttributes = ['name' => 'test-container'];

        $domainNameExtractor = $this->prophesize(DomainNameExtractor::class);
        $domainNameExtractor->provideDomainNames($containerAttributes)->willReturn(true);
        $domainNameExtractor->getDomainNames($containerAttributes)->willReturn(['test.domain.fr']);

        $listener = new ExtractDomainNames($domainNameExtractor->reveal());

        $event = new EventsGetResponse200();
        $event
            ->setType('container')
            ->setAction($containerAction)
            ->setActor((new EventsGetResponse200Actor())->setAttributes(new \ArrayObject($containerAttributes)));

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
            'docker container created event'  => ['create', ContainerCreated::class],
            'docker container removed event' => ['destroy', ContainerRemoved::class],
        ];
    }
}