<?php

namespace ElevenLabs\DockerHostManager\Listener;

use Docker\API\Model\NetworksCreatePostBody;
use Docker\Docker;
use ElevenLabs\DockerHostManager\Event\ApplicationStarted;
use ElevenLabs\DockerHostManager\EventDispatcher\EventListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CreateTraefikNetworkTest extends TestCase
{
    /** @test */
    public function it_implements_the_event_listener_interface()
    {
        $docker = $this->prophesize(Docker::class);
        $listener = new CreateTraefikNetwork($docker->reveal());

        assertThat($listener, isInstanceOf(EventListener::class));
    }

    /** @test */
    public function it_subscribe_to_the_signed_certificate_created_event()
    {
        $docker = $this->prophesize(Docker::class);
        $listener = new CreateTraefikNetwork($docker->reveal());

        assertThat($listener->subscription()->support(new ApplicationStarted()), isTrue());
    }

    /** @test */
    public function it_create_the_traefik_network_is_absent()
    {
        $expectedNetworksCreatePostBody = Argument::allOf(
            Argument::type(NetworksCreatePostBody::class),
            Argument::which('getName', 'traefik')
        );

        $docker = $this->prophesize(Docker::class);
        $docker->networkList()->willReturn([]);
        $docker->networkCreate($expectedNetworksCreatePostBody)->shouldBeCalledTimes(1);

        $listener = new CreateTraefikNetwork($docker->reveal());
        $listener->subscription()->handle(new ApplicationStarted());
    }
}
