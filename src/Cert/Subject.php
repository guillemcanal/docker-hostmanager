<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\Cert;

class Subject
{
    private $organizationName;
    private $commonName;
    private $countryName;
    private $stateOrProvinceName;
    private $localityName;

    public function __construct(
        string $organizationName,
        string $commonName,
        string $countryName,
        string $stateOrProvinceName,
        string $localityName
    ) {
        $this->organizationName = $organizationName;
        $this->commonName = $commonName;
        $this->countryName = $countryName;
        $this->stateOrProvinceName = $stateOrProvinceName;
        $this->localityName = $localityName;
    }

    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    public function getCommonName(): string
    {
        return $this->commonName;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function getStateOrProvinceName(): string
    {
        return $this->stateOrProvinceName;
    }

    public function getLocalityName(): string
    {
        return $this->localityName;
    }
}
