<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\File\File;

class HostsFileManager
{
    private const HOSTS_FILE_FENCED_BLOCK_REGEX = '/#<docker-hostmanager>\n(?P<domainNames>.+?)?#<\/docker-hostmanager>/s';

    /** @var string */
    private $ipv4;

    /** @var File */
    private $hostsFile;

    /** @var array|DomainName[] */
    private $domainNames = [];

    /**
     * @throws \UnexpectedValueException When the hosts file does not exists
     */
    public function __construct(File $hostsFile, string $ipv4 = '127.0.0.1')
    {
        $this->hostsFile = $hostsFile;
        $this->ipv4 = $ipv4;
        if (!$this->hostsFile->exists()) {
            throw new \UnexpectedValueException('The hosts file could not be found');
        }
        if (!$this->hasDockerStackFencedBlock()) {
            $this->addDockerStackFencedBlock();
        } else {
            $this->parseHostsFile();
        }
    }

    private function hasDockerStackFencedBlock(): bool
    {
        return 1 === \preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $this->hostsFile->read());
    }

    private function addDockerStackFencedBlock(): void
    {
        $this->hostsFile->put($this->hostsFile->read()."\n#<docker-hostmanager>\n#</docker-hostmanager>");
    }

    /**
     * @return array|DomainName[]
     */
    public function getDomainNames(): array
    {
        return \array_values($this->domainNames);
    }

    public function hasDomainName(DomainName $domainName): bool
    {
        return \array_key_exists($domainName->getName(), $this->domainNames);
    }

    public function addDomainName(DomainName $domainName): void
    {
        if ($this->hasDomainName($domainName)) {
            $existingDomainName = $this->getDomainNameByName($domainName->getName());
            throw new \UnexpectedValueException(
                \sprintf(
                    'Domain name %s is already associated with %s',
                    $existingDomainName->getName(),
                    $existingDomainName->getContainerName()
                )
            );
        }

        $this->domainNames[$domainName->getName()] = $domainName->withIpv4($this->ipv4);
    }

    public function removeDomainName(DomainName $domainNameToRemove): void
    {
        foreach ($this->domainNames as $i => $domainName) {
            if ($domainName->equals($domainNameToRemove)) {
                unset($this->domainNames[$i]);
                break;
            }
        }
    }

    public function updateHostsFile(): void
    {
        $domainNameLines = \array_map(
            function (DomainName $domainName) {
                return $domainName->toString();
            },
            $this->domainNames
        );

        $contents = \preg_replace_callback(
            self::HOSTS_FILE_FENCED_BLOCK_REGEX,
            function () use ($domainNameLines) {
                return \sprintf(
                    "#<docker-hostmanager>\n%s\n#</docker-hostmanager>",
                    \implode("\n", $domainNameLines)
                );
            },
            $this->hostsFile->read()
        );

        $this->hostsFile->put($contents);
    }

    private function parseHostsFile(): void
    {
        $contents = $this->hostsFile->read();

        \preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $contents, $matches);
        if (!\array_key_exists('domainNames', $matches)) {
            return;
        }

        $rawDomains = \explode("\n", $matches['domainNames']);
        foreach ($rawDomains as $rawDomain) {
            if ('' === $rawDomain) {
                continue;
            }
            $this->addDomainName(DomainName::fromString($rawDomain));
        }
    }

    public function getDomainNameByName(string $name): DomainName
    {
        return $this->domainNames[$name];
    }
}
