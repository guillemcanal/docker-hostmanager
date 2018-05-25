<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Cert;

class Subject
{
    private $organizationName;
    private $commonName;
    private $countryName;
    private $stateProvinceName;
    private $localityName;

    public function __construct(
        string $organizationName,
        string $commonName,
        string $countryName,
        string $stateProvinceName,
        string $localityName
    ) {
        $this->organizationName  = $organizationName;
        $this->commonName        = $commonName;
        $this->countryName       = $countryName;
        $this->stateProvinceName = $stateProvinceName;
        $this->localityName      = $localityName;
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

    public function getStateProvinceName(): string
    {
        return $this->stateProvinceName;
    }

    public function getLocalityName(): string
    {
        return $this->localityName;
    }
}
