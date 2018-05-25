<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\File\FileHandler;

class HostsFileManager
{
    private const HOSTS_FILE_FENCED_BLOCK_REGEX = '/#<docker-stack>\n(.+?)?(?=#<\/docker-stack>)/s';

    /** @var string */
    private $hostIpAddress;

    /** @var FileHandler */
    private $hostsFile;

    /** @var string[] */
    private $hostnames = [];

    /**
     * @throws \UnexpectedValueException When the hosts file does not exists
     */
    public function __construct(FileHandler $hostsFile, string $hostIpAddress = '127.0.0.1')
    {
        $this->hostsFile = $hostsFile;
        $this->hostIpAddress = $hostIpAddress;
        if (!$this->hostsFile->exists()) {
            throw new \UnexpectedValueException('The hosts file could not be found');
        }
        if (!$this->hasDockerStackFencedBlock()) {
            $this->addDockerStackFencedBlock();
        }
        $this->parseHostsFile();
    }

    private function hasDockerStackFencedBlock(): bool
    {
        return preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $this->hostsFile->read()) === 1;
    }

    private function addDockerStackFencedBlock(): void
    {
        $this->hostsFile->put(
            $this->hostsFile->read()
            . "\n#<docker-stack>\n#</docker-stack>\n"
        );
    }

    public function hasDomainName(string $domainName): bool
    {
        return \in_array($domainName, $this->hostnames, true);
    }

    public function addDomainName(string $domainName): void
    {
        if (!$this->hasDomainName($domainName)) {
            $this->hostnames[] = $domainName;
        }
    }

    public function clear(): void
    {
        if ($this->hasDockerStackFencedBlock()) {
            $contents = preg_replace_callback(
                self::HOSTS_FILE_FENCED_BLOCK_REGEX,
                function () {
                    return "#<docker-stack>\n";
                },
                $this->hostsFile->read()
            );
            $this->hostsFile->put($contents);
        }
    }

    public function removeDomainName(string $domainName): void
    {
        if ($this->hasDomainName($domainName)) {
            unset($this->hostnames[array_search($domainName, $this->hostnames, true)]);
        }
    }

    public function updateHostsFile(): void
    {
        $domains = implode(
            "\n",
                array_map(
                function (string $domain) {
                    return $this->hostIpAddress . ' ' . $domain;
                },
                $this->hostnames
            )
        );

        $contents = preg_replace_callback(
            self::HOSTS_FILE_FENCED_BLOCK_REGEX,
            function () use ($domains) {
                return "#<docker-stack>\n" . $domains . "\n";
            },
            $this->hostsFile->read()
        );

        $this->hostsFile->put($contents);
    }

    private function parseHostsFile(): void
    {
        $contents = $this->hostsFile->read();

        preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $contents, $matches);
        if (!array_key_exists(1, $matches)) {
            return;
        }

        $rawDomains = explode("\n", $matches[1]);
        foreach ($rawDomains as $rawDomain) {
            if ($rawDomain === '' || $rawDomain[0] === '#') {
                continue;
            }
            $matched = preg_split('/\s+/', $rawDomain);
            if (\count($matched) !== 2) {
                throw new \RuntimeException('Expected exactly one IP address and one hostname, got ' . $rawDomain);
            }
            $this->addDomainName($matched[1]);
        }
    }
}