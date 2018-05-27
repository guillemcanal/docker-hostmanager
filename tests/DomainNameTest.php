<?php

namespace ElevenLabs\DockerHostManager;

use PHPUnit\Framework\TestCase;

class DomainNameTest extends TestCase
{
    /** @test */
    public function it should be constructed with a domain name and a container name()
    {
        $domainName = new DomainName('dev.foo.fr', 'foo');

        assertThat($domainName->getName(), equalTo('dev.foo.fr'));
        assertThat($domainName->getContainerName(), equalTo('foo'));
    }

    /** @test */
    public function it throw an exception when trying to get the string representation of the domain name without an ipv4()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The domain name is not mapped to an ipv4');

        (new DomainName('dev.foo.fr', 'foo'))->toString();
    }

    /** @test */
    public function it can parse a domain name from a string()
    {
        $domainName = DomainName::fromString('127.0.0.1 dev.foo.fr #container-name');

        assertThat($domainName->getIpv4(), equalTo('127.0.0.1'));
        assertThat($domainName->getName(), equalTo('dev.foo.fr'));
        assertThat($domainName->getContainerName(), equalTo('container-name'));
    }

    /** @test */
    public function it throw an exception when the parsed domain name string is invalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse the container domain string: invalid string');

        DomainName::fromString('invalid string');
    }
}