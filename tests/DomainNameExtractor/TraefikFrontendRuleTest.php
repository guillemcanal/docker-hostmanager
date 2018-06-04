<?php

namespace ElevenLabs\DockerHostManager\DomainNameExtractor;

use PHPUnit\Framework\TestCase;

class TraefikFrontendRuleTest extends TestCase
{
    /** @test */
    public function it_should_extract_domain_names_from_a_traefik_frontend_rule_label()
    {
        $containerAttributes = ['traefik.frontend.rule' => 'Host:dev.foo.fr,dev.bar.fr,dev.baz.fr; Path:/hello'];

        $traefikFrontendRule = new TraefikFrontendRule();
        $provideDomainNames = $traefikFrontendRule->provideDomainNames($containerAttributes);

        $actualDomainNames = $traefikFrontendRule->getDomainNames($containerAttributes);
        $expectedDomainNames = ['dev.foo.fr', 'dev.bar.fr', 'dev.baz.fr'];

        assertTrue($provideDomainNames);
        assertThat($actualDomainNames, equalTo($expectedDomainNames));
    }

    /** @test */
    public function it_does_not_provide_domain_names_when_the_traefik_frontend_rule_label_is_absent()
    {
        $containerAttributes = [];
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->provideDomainNames($containerAttributes));
    }

    /** @test */
    public function it_does_not_provide_domain_names_when_the_traefik_frontend_rule_does_not_contains_an_host_rule()
    {
        $containerAttributes = ['traefik.frontend.rule' => 'HostRegexp:{subdomain:[a-z]+}.localhost; Path:/hello'];
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->provideDomainNames($containerAttributes));
    }

    /** @test */
    public function it_does_not_provide_domain_names_when_the_traefik_frontend_rule_is_invalid()
    {
        $containerAttributes = ['traefik.frontend.rule' => 'invalid'];
        $traefikFrontendRule = new TraefikFrontendRule();

        assertFalse($traefikFrontendRule->provideDomainNames($containerAttributes));
    }

}