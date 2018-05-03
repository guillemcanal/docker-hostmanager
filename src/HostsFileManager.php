<?php declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

use ElevenLabs\DockerHostManager\File\FileHandler;

class HostsFileManager
{
    private const HOSTS_FILE_FENCED_BLOCK_REGEX = '/#<docker-stack>\n(.+?)?(?=#<\/docker-stack>)/s';

    /** @var string */
    private $ipAddress;

    /** @var FileHandler */
    private $hostsFile;

    /** @var string[] */
    private $domains = [];

    /**
     * @throws \UnexpectedValueException When the hosts file does not exists
     */
    public function __construct(FileHandler $hostsFile, string $ipAdress = '127.0.0.1')
    {
        $this->hostsFile = $hostsFile;
        $this->ipAddress = $ipAdress;
        if (!$this->hostsFile->exists()) {
            throw new \UnexpectedValueException('The hosts file could not be found');
        }
        if (!$this->hostsFileContainsFencedBlock()) {
            $this->addHostsFileFencedBlock();
        }
        $this->parseHostsFile();
    }

    private function hostsFileContainsFencedBlock(): bool
    {
        return preg_match(self::HOSTS_FILE_FENCED_BLOCK_REGEX, $this->hostsFile->getContents()) === 1;
    }

    private function addHostsFileFencedBlock(): void
    {
        $this->hostsFile->putContents(
            $this->hostsFile->getContents()
            . "\n#<docker-stack>\n#</docker-stack>\n"
        );
    }

    public function hasDomain(string $domain): bool
    {
        return \in_array($domain, $this->domains, true);
    }

    public function addDomain(string $domain): void
    {
        if (!$this->hasDomain($domain)) {
            $this->domains[] = $domain;
        }
    }

    public function removeDomain(string $domain): void
    {
        if ($this->hasDomain($domain)) {
            unset($this->domains[array_search($domain, $this->domains, true)]);
        }
    }

    public function updateHostsFile(): void
    {
        $domains = implode(
            "\n",
                array_map(
                function (string $domain) {
                    return $this->ipAddress . ' ' . $domain;
                },
                $this->domains
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
                throw new \RuntimeException('Expected exactly one IP address and one domain, got ' . $rawDomain);
            }
            $this->addDomain($matched[1]);
        }
    }
}