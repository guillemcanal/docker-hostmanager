<?php

declare(strict_types = 1);

namespace ElevenLabs\DockerHostManager\Cert;

use PHPUnit\Framework\TestCase;

class SubjectTest extends TestCase
{
    /** @test */
    public function it provide certificate subject values()
    {
        $subject = new Subject(
            $organizationName    = 'ACME Inc.',
            $commonName          = 'ACME Root CA',
            $countryName         = 'FR',
            $stateOrProvinceName = 'Paris',
            $localityName        = 'Paris'
        );

        assertThat($subject->getOrganizationName(), equalTo($organizationName));
        assertThat($subject->getCommonName(), equalTo($commonName));
        assertThat($subject->getCountryName(), equalTo($countryName));
        assertThat($subject->getStateOrProvinceName(), equalTo($stateOrProvinceName));
        assertThat($subject->getLocalityName(), equalTo($localityName));
    }
}