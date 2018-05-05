<?php

namespace ElevenLabs\DockerHostManager\HostsProvider;

use PHPUnit\Framework\TestCase;

class TraefikHostsProviderTest extends TestCase
{
    /** @test */
    public function it should extract hostnames from a traefik frontend rule label()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'Host:dev.foo.fr,dev.bar.fr,dev.baz.fr; Path:/hello']
        );

        $traefikHostProvider = new TraefikHostsProvider();
        $shouldProvideHosts = $traefikHostProvider->hasHosts($containerAttributes);

        $actualHosts = $traefikHostProvider->getHosts($containerAttributes);
        $expectedHosts = ['dev.foo.fr', 'dev.bar.fr', 'dev.baz.fr'];

        assertTrue($shouldProvideHosts);
        assertThat($actualHosts, equalTo($expectedHosts));
    }

    /** @test */
    public function it does not provide hostnamess when the traefik frontend rule label is absent()
    {
        $containerAttributes = new \ArrayObject();
        $traefikHostProvider = new TraefikHostsProvider();

        assertFalse($traefikHostProvider->hasHosts($containerAttributes));
    }

    /** @test */
    public function it does not provide hostnames when the traefik frontend rule label does not contains an host rule()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'HostRegexp:{subdomain:[a-z]+}.localhost; Path:/hello']
        );
        $traefikHostProvider = new TraefikHostsProvider();

        assertFalse($traefikHostProvider->hasHosts($containerAttributes));
    }
}