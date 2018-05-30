<?php

declare(strict_types=1);

namespace ElevenLabs\DockerHostManager;

class Container
{
    private $name;
    private $labels;

    public function __construct($name, ?array $labels)
    {
        $this->name = $name;
        $this->labels = $labels ?: [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
