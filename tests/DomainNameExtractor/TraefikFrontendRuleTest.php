<?php

namespace ElevenLabs\DockerHostManager\DomainNameExtractor;

use PHPUnit\Framework\TestCase;

class TraefikFrontendRuleTest extends TestCase
{
    /** @test */
    public function it should extract domain names from a traefik frontend rule label()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'Host:dev.foo.fr,dev.bar.fr,dev.baz.fr; Path:/hello']
        );

        $traefikFrontendRule = new TraefikFrontendRule();
        $provideDomainNames = $traefikFrontendRule->provideDomainNames($containerAttributes);

        $actualDomainNames = $traefikFrontendRule->getDomainNames($containerAttributes);
        $expectedDomainNames = ['dev.foo.fr', 'dev.bar.fr', 'dev.baz.fr'];

        assertTrue($provideDomainNames);
        assertThat($actualDomainNames, equalTo($expectedDomainNames));
    }

    /** @test */
    public function it does not provide domain names when the traefik frontend rule label is absent()
    {
        $containerAttributes = new \ArrayObject();
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->provideDomainNames($containerAttributes));
    }

    /** @test */
    public function it does not provide domain names when the traefik frontend rule label does not contains an host rule()
    {
        $containerAttributes = new \ArrayObject(
            ['traefik.frontend.rule' => 'HostRegexp:{subdomain:[a-z]+}.localhost; Path:/hello']
        );
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->provideDomainNames($containerAttributes));
    }
}