<?php

namespace ElevenLabs\DockerHostManager\HostsExtractor;

use PHPUnit\Framework\TestCase;

class TraefikFrontendRuleTest extends TestCase
{
    /** @test */
    public function it should extract hostnames from a traefik frontend rule label()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'Host:dev.foo.fr,dev.bar.fr,dev.baz.fr; Path:/hello']
        );

        $traefikFrontendRule = new TraefikFrontendRule();
        $shouldProvideHosts = $traefikFrontendRule->hasHosts($containerAttributes);

        $actualHosts = $traefikFrontendRule->getHosts($containerAttributes);
        $expectedHosts = ['dev.foo.fr', 'dev.bar.fr', 'dev.baz.fr'];

        assertTrue($shouldProvideHosts);
        assertThat($actualHosts, equalTo($expectedHosts));
    }

    /** @test */
    public function it does not provide hostnamess when the traefik frontend rule label is absent()
    {
        $containerAttributes = new \ArrayObject();
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->hasHosts($containerAttributes));
    }

    /** @test */
    public function it does not provide hostnames when the traefik frontend rule label does not contains an host rule()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'HostRegexp:{subdomain:[a-z]+}.localhost; Path:/hello']
        );
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->hasHosts($containerAttributes));
    }
}