<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager\DomainNameExtractor;

/**
 * Extract domain names from the `traefik.frontend.rule` Traefik Docker label.
 */
class TraefikFrontendRule implements DomainNameExtractor
{
    public const TRAEFIK_FRONTEND_RULE = 'traefik.frontend.rule';

    private $domainNames = [];

    public function provideDomainNames(\ArrayObject $attributes): bool
    {
        if (!\array_key_exists(self::TRAEFIK_FRONTEND_RULE, $attributes)) {
            return false;
        }
        $parsedFrontendRules = $this->parseFrontendRules($attributes[self::TRAEFIK_FRONTEND_RULE]);
        if (!\array_key_exists('Host', $parsedFrontendRules)) {
            return false;
        }

        $this->domainNames = $parsedFrontendRules['Host'];

        return true;
    }

    public function getDomainNames(\ArrayObject $attributes): array
    {
        return $this->domainNames;
    }

    private function parseFrontendRules(string $frontEndRulesString): array
    {
        $frontEndRules = [];
        $sections = \explode(';', $frontEndRulesString);
        foreach ($sections as $section) {
            $parts = \explode(':', $section);
            $key = \trim($parts[0]);

            $values = \array_map(
                function (string $value) {
                    return \trim($value);
                },
                \explode(',', $parts[1] ?? '')
            );
            $frontEndRules[$key] = $values;
        }

        return $frontEndRules;
    }
}
