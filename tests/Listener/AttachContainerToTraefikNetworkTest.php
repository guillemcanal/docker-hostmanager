<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\Network;
use Docker\API\Model\NetworkContainer;
use Docker\API\Model\NetworksIdConnectPostBody;
use Docker\Docker;
use ElevenLabs\DockerHostManager\Event\ContainerCreated;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class AttachContainerToTraefikNetworkTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $docker = $this->prophesize(Docker::class);
        $listener = new AttachContainerToTraefikNetwork($docker->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subcribe_to_the_container_created_event()
    {
        $docker = $this->prophesize(Docker::class);
        $listener = new AttachContainerToTraefikNetwork($docker->reveal());

        assertThat($listener->subscription()->support(new ContainerCreated('', [], [])), isTrue());
    }

    /** @test */
    public function it_attach_a_container_to_the_traefik_network()
    {
        $expectedNetworkConnectBody = Argument::allOf(
            Argument::type(NetworksIdConnectPostBody::class),
            Argument::which('getContainer', 'test-container')
        );

        $traefikNetwork = (new Network())->setContainers(
            new \ArrayObject([(new NetworkContainer())->setName('another-container')])
        );

        $docker = $this->prophesize(Docker::class);
        $docker->networkInspect('traefik')->willReturn($traefikNetwork);
        $docker->networkConnect('traefik', $expectedNetworkConnectBody)->shouldBeCalledTimes(1);

        $listener = new AttachContainerToTraefikNetwork($docker->reveal());
        $containerCreated = new ContainerCreated('test-container', [], []);

        $listener->subscription()->handle($containerCreated);
    }

    /** @test */
    public function it_does_not_attach_a_container_to_the_traefik_network_if_already_attached()
    {
        $traefikNetwork = (new Network())->setContainers(
            new \ArrayObject([(new NetworkContainer())->setName('test-container')])
        );

        $docker = $this->prophesize(Docker::class);
        $docker->networkInspect('traefik')->willReturn($traefikNetwork);
        $docker->networkConnect(Argument::cetera())->shouldNotBeCalled();

        $listener = new AttachContainerToTraefikNetwork($docker->reveal());
        $containerCreated = new ContainerCreated('test-container', [], []);

        $listener->subscription()->handle($containerCreated);
    }
}
