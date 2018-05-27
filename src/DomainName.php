<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

/**
 * A mapped domain name to an v4 IP address associated with a container name
 */
class DomainName
{
    private const STRING_PATTERN =
        '/^(?P<ipv4>(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))'
        . '\s(?P<domain>[a-z0-9-.]+)'
        . '\s#(?P<containerName>[a-zA-Z0-9][a-zA-Z0-9_.-]+)$/';

    private $ipv4;
    private $name;
    private $containerName;

    public function __construct(string $name, string $containerName)
    {
        $this->name = $name;
        $this->containerName = $containerName;
    }

    public static function fromString(string $string): self
    {
        if (preg_match(self::STRING_PATTERN, $string, $matched) !== 1) {
            throw new \InvalidArgumentException('Unable to parse the container domain string: ' . $string);
        }

        return (new self($matched['domain'], $matched['containerName']))->withIpv4($matched['ipv4']);
    }

    public function toString(): string
    {
        return sprintf('%s %s #%s', $this->getIpv4(), $this->getName(), $this->getContainerName());
    }

    public function withIpv4(string $ipv4): self
    {
        $new = clone $this;
        $new->ipv4 = $ipv4;

        return $new;
    }

    public function getIpv4(): string
    {
        if ($this->ipv4 === null) {
            throw new \LogicException('The domain name is not mapped to an ipv4');
        }

        return $this->ipv4;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function equals(DomainName $domainName): bool
    {
        return $domainName->getName() === $this->name
            && $domainName->getContainerName() === $this->containerName;
    }
}