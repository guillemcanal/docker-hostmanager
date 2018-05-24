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
        return preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $this->hostsFile->getContents()) === 1;
    }

    private function addDockerStackFencedBlock(): void
    {
        $this->hostsFile->putContents(
            $this->hostsFile->getContents()
            . "\n#<docker-stack>\n#</docker-stack>\n"
        );
    }

    public function hasHostname(string $hostname): bool
    {
        return \in_array($hostname, $this->hostnames, true);
    }

    public function addHostname(string $hostname): void
    {
        if (!$this->hasHostname($hostname)) {
            $this->hostnames[] = $hostname;
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
                $this->hostsFile->getContents()
            );
            $this->hostsFile->putContents($contents);
        }
    }

    public function removeHostname(string $hostname): void
    {
        if ($this->hasHostname($hostname)) {
            unset($this->hostnames[array_search($hostname, $this->hostnames, true)]);
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
            $this->hostsFile->getContents()
        );

        $this->hostsFile->putContents($contents);
    }

    private function parseHostsFile(): void
    {
        $contents = $this->hostsFile->getContents();

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
            $this->addHostname($matched[1]);
        }
    }
}