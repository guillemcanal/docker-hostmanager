<?php

namespace ElevenLabs\DockerHostManager;

use PHPUnit\Framework\TestCase;

class DomainNameTest extends TestCase
{
    /** @test */
    public function it_should_be_constructed_with_a_domain_name_and_a_container_name()
    {
        $domainName = new DomainName('dev.foo.fr', 'foo');

        assertThat($domainName->getName(), equalTo('dev.foo.fr'));
        assertThat($domainName->getContainerName(), equalTo('foo'));
    }

    /** @test */
    public function it_throw_an_exception_when_trying_to_get_the_string_representation_of_the_domain_name_without_an_ipv4()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The domain name is not mapped to an ipv4');

        (new DomainName('dev.foo.fr', 'foo'))->toString();
    }

    /** @test */
    public function it_can_parse_a_domain_name_from_a_string()
    {
        $domainName = DomainName::fromString('127.0.0.1 dev.foo.fr #container-name');

        assertThat($domainName->getIpv4(), equalTo('127.0.0.1'));
        assertThat($domainName->getName(), equalTo('dev.foo.fr'));
        assertThat($domainName->getContainerName(), equalTo('container-name'));
    }

    /** @test */
    public function it_throw_an_exception_when_the_parsed_domain_name_string_is_invalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse the container domain string: invalid string');

        DomainName::fromString('invalid string');
    }
}